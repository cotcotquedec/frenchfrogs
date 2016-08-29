<?php namespace Frenchfrogs\App\Console;

use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Composer;

class CodeActionCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'code:action
                             {method : Méthode pour l\'action (post, get, etc...)}
                             {name : Nom de l\action}
                             {controller? : Nom du controller en minuscule}
                             {--acl= : Permission pour l\'accès}
                             {--params= : Paramètre pour l\'action}
                             {--template=default : Template utilisé pour le body de la méthode}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = "Création d'une action pour le controller";


    /**
     * Params
     *
     * @var
     */
    protected $params;


    /**
     * Setter for $params
     *
     * @param array $params
     * @return $this
     */
    public function setParams(array $params)
    {
        $this->params = $params;
        return $this;
    }

    /**
     * Getter for $params
     *
     * @return mixed
     */
    public function getParams()
    {
        if (!isset($this->params)) {
            $this->extractParams();
        }

        return $this->params;
    }

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }


    /**
     * Extract params
     *
     * @return $this
     * @throws \Exception
     */
    protected function extractParams()
    {
        // Recuperation des paramètres de la commande
        $p = $this->option('params');

        // INITALISATION
        $params = [];

        if (!empty($p)) {

            // EXPLODE PARAMS
            foreach (explode('#', $p) as $param) {
                $parts = explode(';', $param);

                // paramètres
                $variable = array_shift($parts);

                $match = [];
                if (!preg_match('#((?<type>\w+)\|)?(?<name>\w+)(=(?<value>.+))?#', $variable, $match)) {
                    exc('Impossible de determiner les paramètres : ' . $variable);
                }

                // recuperation du nom du paramètre
                $name = $match['name'];

                // initialisation du container
                $params[$name] = [];

                // TYPE - CLASS
                if (isset($match['type'])) {
                    $params[$name]['type'] = $match['type'];
                }

                // DEFAULT VALUE
                if (isset($match['value'])) {
                    $params[$name]['value'] = $match['value'];
                }

                // VALIDATOR
                if ($validator = array_shift($parts)) {
                    if (!empty($validator)) {
                        $params[$name]['validator'] = $validator;
                    }
                }

                // FILTER
                if ($filter = array_shift($parts)) {
                    if (!empty($filter)) {
                        $params[$name]['filter'] = $filter;
                    }
                }
            }
        }

        $this->setParams($params);

        return $this;
    }

    /**
     * Génération des paramètres de la méthode
     *
     * @param PhpMethod $method
     * @return $this
     */
    protected function generateParams(PhpMethod $method)
    {
        // PARAMS
        foreach ($this->getParams() as $name => $info) {
            $param = PhpParameter::create($name);

            if (isset($info['type'])) {
                $param->setType($info['type']);
            }

            if (isset($info['value'])) {
                // Cas de la valeur non obligatoire
                if ($info['value'] == 'null') {
                    $info['value'] = null;
                }
                $param->setValue($info['value']);
            }
            $method->addParameter($param);
        }

        return $this;
    }


    /**
     * Generate ACL block
     *
     */
    protected function generateAcl()
    {
        $body = '';

        $ruler = [];
        $acl = $this->option('acl') ?: 'null';
        $ruler[] = $acl;

        // RULER
        $validator = $filter = [];
        foreach ($this->getParams() as $name => $info) {
            $validator[] = sprintf("['%s' => '%s']", $name, $info['validator']);
            if (!empty($info['filter'])) {
                $filter[] = sprintf("['%s' => f(\$%s, '%s')]", $name, $name, $info['filter']);
            } else {
                $filter[]=  sprintf("['%s' => \$%s]", $name, $name);
            }
        }

        // VALIDATOR
        if (!empty($validator)) {
            $validator = implode(',',$validator);
            $filter = implode(',',$filter);
            $ruler[] = $validator;
            $ruler[] = $filter;
        }

        $body .= str_repeat(PHP_EOL, 2);

        // RENDER
        if ($acl || $validator) {
            $body .= '//RULER' . PHP_EOL;
            $body .= sprintf("\\ruler()->check(%s);", PHP_EOL . implode(',' . PHP_EOL, $ruler) . PHP_EOL);
            $body .= str_repeat(PHP_EOL, 2);
        }

        return $body;
    }


    /**
     * Generation du body pour le template "default"
     *
     * @return string
     */
    protected function templateDefault()
    {
        return "return basic('__TITLE__', '__CONTENT__');";
    }

    /**
     * Generation du body pour le template "default"
     *
     * @return string
     */
    protected function templateForm()
    {
        $body = file_get_contents(__DIR__ . '/stubs/actions/form.stub');
        return $body;
    }

    /**
     * Generation du body pour le template "default"
     *
     * @return string
     */
    protected function templateDelete()
    {
        $body = file_get_contents(__DIR__ . '/stubs/actions/delete.stub');
        return $body;
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $this->info('Nous allons créer ensemble une action pour un controller');


        dd('Pas prêt, i ll be back');



        $controller = $this->argument('controller');

        // création de la méthode
        $method = camel_case($this->argument('method') .  '_' . $this->argument('name'));
        $method = PhpMethod::create($method);

        // PARAMS
        $this->generateParams($method);

        // BODY
        $body = '';
        $body .= $this->generateAcl();

        // TEMPLATE
        $template = camel_case('template_' . $this->option('template'));
        if (!method_exists($this, $template)) {
            exc('Impossible de trouver le template : ' . $template);
        }
        $body .= call_user_func([$this, $template]);
        $method->setBody($body);

        // DOCKBOCK
        $dockblock = new Docblock();
        $dockblock->appendTag(TagFactory::create('name', 'Artisan'));
        $dockblock->appendTag(TagFactory::create('see', 'php artisan ffmake:action'));
        $dockblock->appendTag(TagFactory::create('generated', Carbon::now()));
        $method->setDocblock($dockblock);

        // CONTROLLER
        $controller = ucfirst(camel_case($controller . '_controller'));
        $controller  = new \ReflectionClass('App\\Http\\Controllers\\'.$controller);

        $class = PhpClass::fromReflection($controller)->setMethod($method);
        $class->setParentClassName('Controller');// fix la gestion des namespaec pour la parent class

        // GENERATION
        $generator = new CodeGenerator();
        $class = '<?php ' . $generator->generate($class);

        // inscription du code dans la classe
        file_put_contents($controller->getFileName(), $class);

        $this->info('Action generated dans le fichier : ' . $controller->getFileName());
    }


}
