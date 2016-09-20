<?php namespace FrenchFrogs\App\Console;

use FrenchFrogs\Maker\Maker;
use FrenchFrogs\Maker\Method;
use FrenchFrogs\Maker\Parameter;
use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Composer;
use Models\Acl;

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
     *
     */
    const CHOICE_NEW = ' > Nouveau';

    /**
     *
     *
     */
    const CHOICE_NO_MORE = '> Fini';

    /**
     *
     *
     *
     */
    const CHOISE_NULL = 'null';


    protected $templates = [
        '_basic' => 'Basique',
        '_form' => 'Formulaire',
        '_delete' => 'Suppression'
    ];

    /**
     *
     * @var Method
     */
    protected $method;


    /**
     * Class de gestion du controller
     *
     * @var Maker
     */
    protected $controller;

    /**
     *
     *
     * @var array
     */
    protected $validators = [];

    /**
     *
     *
     * @var array
     */
    protected $filters = [];

    /**
     * Getter for $validators
     *
     * @return array
     */
    public function getValidators()
    {
        return $this->validators;
    }

    /**
     * Ajout d'un validateur
     *
     * @param $name
     * @param $validator
     * @return $this
     */
    public function addValidator($name, $validator)
    {
        $this->validators[$name] = $validator;
        return $this;
    }

    /**
     * Reurn TRUE si il y a des validateurs
     *
     * @return bool
     */
    public function hasValidator()
    {
        return !empty($this->validators);
    }

    /**
     * GEtter for $filters
     *
     * @return array
     */
    public function getFilters()
    {
        return $this->filters;
    }


    /**
     * Ajout d'un filtre
     *
     * @param $name
     * @param $filter
     * @return $this
     */
    public function addFilter($name, $filter)
    {
        $this->filters[$name] = $filter;
        return $this;
    }


    /**
     * Getter for $method
     *
     * @return Method
     */
    public function getMethod()
    {
        return $this->method;
    }

    /**
     * Setter for $method
     *
     * @param Method $method
     * @return $this
     */
    public function setMethod(Method $method)
    {
        $this->method = $method;
        return $this;
    }

    /**
     * Getter for $controller
     *
     * @return Maker
     */
    public function getController()
    {
        return $this->controller;
    }

    /**
     * Setter for $controller
     *
     * @param Maker $controller
     * @return $this
     */
    public function setController(Maker $controller)
    {
        $this->controller = $controller;
        return $this;
    }

    /**
     * Generation du body pour le template "default"
     *
     * @return string
     */
    protected function _basic()
    {
        $this->info('Configuration du template basique');

        $title = $this->ask('Titre?');
        $content = $this->ask('Content?');

        $this->getMethod()->appendBody(sprintf("%sreturn basic('%s', '%s');", str_repeat(PHP_EOL, 2), $title, $content));
    }

    /**
     * Generation du body pour le template "default"
     *
     * @return string
     */
    protected function _form()
    {
        $body = file_get_contents(__DIR__ . '/stubs/actions/form.stub');
        return $body;
    }

    /**
     * Generation du body pour le template "default"
     *
     * @return string
     */
    protected function _delete()
    {
        $body = file_get_contents(__DIR__ . '/stubs/actions/delete.stub');
        return $body;
    }


    /**
     *
     * @return Maker
     */
    protected function controller()
    {
        do {
            // recuperation des controllers
            $controllers = Maker::findControllers();

            // Ajout du choix de création d'un controller
            array_unshift($controllers, static::CHOICE_NEW);

            // question
            $controller = $this->choice('Dans quel controller voulez vous créer cette action?', $controllers, 0);

            // Création d'un nouveau controller
            if ($controller == static::CHOICE_NEW) {
                unset($controller);
                $name = $this->ask('Le nom de votre controller');
                $this->call('code:controller', ['name' => $name]);
            }
        } while(empty($controller));

        // Chemin complet du controller
        $controller = '\\' . Maker::NAMESPACE_CONTROLLER . $controller;

        // INIT MAKER;
        $this->setController($controller = Maker::load($controller));

        return $controller;
    }


    /**
     * Génration des paramètres
     */
    protected function params()
    {
        do {
            if ($while = $this->confirm('Voulez-vous ajouter un paramètre?', false)){

                // NOM
                $param = $this->ask('Quel est le nom du paramètre ($___) ?');

                // MAKER PARAM
                $param = new Parameter($name = camel_case($param));

                // TYPE
                if ($this->confirm('A-t-il un Type (classe)?')) {
                    $type = $this->ask('Quel est le type (classe) du parmètre ' . $name);
                    $param->setType($type);
                }

                // DEFAULT
                if ($this->confirm('A-t-il une valeur par défaut?')) {
                    $value = $this->ask('Quelle est elle?', static::CHOISE_NULL);
                    $param->setDefault($value == static::CHOISE_NULL ? null : $value);
                }

                // VALIDATOR
                if ($this->confirm('Doit il être validé?')) {
                    $validator = $this->ask('Saisir la chaine laravel de validation?');
                    $this->addValidator($name, sprintf("'%s' => '%s'", $name, $validator));


                    // FILTER
                    $filter = $this->ask('Saisir la chaine de filtrage si présente?', static::CHOISE_NULL);

                    if ($filter != static::CHOISE_NULL) {
                        $filter = sprintf("'%s' => f(\$%s, '%s')", $name, $name, $filter);
                    } else {
                        $filter =  sprintf("'%s' => \$%s", $name, $name);
                    }

                    $this->addFilter($name, $filter);
                }

                // ajout du paramètre a la méthode
                $this->getMethod()->addParameter($param);
                $this->warn('Parmètre ' . $name .  ' Ajouté');
            }
        } while($while);
    }


    /**
     * Génération des acl
     *
     * @return $this
     */
    protected function acl()
    {
        $body = '';

        $permission = false;
        if($this->confirm('Faut il une permission pour acceder l\'action?', true)) {

            do {
                // recuperation des ACL
                $rulerClass = $this->ask('Quelle est la classe de gestion des Acl?', configurator()->get('ruler.class'));
                $ruler = Maker::load($rulerClass);

                // ANALYSE DES CONSTANTES
                $permissions = $ruler->getPermissionsConstants();
                array_unshift($permissions, static::CHOICE_NEW);
                $permission = $this->choice('Permissions?', $permissions, 0);

                // Cas d'une nouvelle persmission
                if ($permission == static::CHOICE_NEW) {
                    $this->call('code:permission');
                    $permission = false;
                }

            } while(empty($permission));
        }

        // VALIDATOR
        $validator = '';
        if ($this->hasValidator()) {
            $validator = sprintf(', [%s], [%s]', implode(',', $this->getValidators()), implode(',', $this->getFilters()));
        }

        $body .= str_repeat(PHP_EOL, 2);

        // RENDER
        if ($permission || $validator) {
            $this->getController()->addAlias('Acl', Acl::class);
            $body .= '//RULER' . PHP_EOL;
            $body .= sprintf("\\ruler()->check(Acl::%s %s);", $permission, $validator);
            $body .= str_repeat(PHP_EOL, 2);
        }

        $this->getMethod()->appendBody($body);

        return $this;
    }


    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        // validation declaration
        $validator = \Validator::make(
            [
                'method' => $method = $this->argument('method'),
                'name' => $name = $this->argument('name')

            ],
            [
                'method' => 'required|in:get,post,delete,any',
                'name' => 'required'
            ]
        );

        // check if argument are valid
        if ($validator->fails()) {
            $this->error($validator->getMessageBag()->toJson());
            return 1;
        }

        $this->info('Nous allons créer ensemble une action');

        // CONTROLLER
        $controller = $this->controller();

        // METHOD
        $method = camel_case($method .  '_' . $name);

        if ($controller->hasMethod($method)) {
            if (!$this->confirm('La méthode "'.$method.'" existe déjà, voulez vous l\'écraser?')) {
                $this->info('A plus tard!!');
                return 1;
            }
        }

        // Création de la méthode
        $method = $controller->addMethod($method);
        $description = $this->ask('Description de l\'action');
        $method->setDescription($description);
        $this->setMethod($method);

        // PARAMS
        $this->params();
        $this->acl();

        // TEMPLATE

        if ($this->confirm('Souhaitez vous charger un template pour cette action?')) {
            $template = $this->choice('Quel template voulez vous charger ?', array_values($this->templates));
            $template = array_search($template, $this->templates);
            $this->$template();
        }

        // ecriture dans le fichier
        $controller->write();
    }
}
