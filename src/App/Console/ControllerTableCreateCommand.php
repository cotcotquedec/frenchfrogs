<?php namespace Frenchfrogs\App\Console;

use Carbon\Carbon;
use gossi\codegen\generator\CodeGenerator;
use gossi\codegen\model\PhpClass;
use gossi\codegen\model\PhpMethod;
use gossi\codegen\model\PhpParameter;
use gossi\docblock\Docblock;
use gossi\docblock\tags\TagFactory;
use Illuminate\Console\Command;

class ControllerTableCreateCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'ffmake:table
                             {controller : Nom du controller en minuscule}
                             {name : Nom de la method}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = "Création d'une methode static table dans un controller";

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
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        // recuperation du controller
        $controller = $this->argument('controller');

        // création de la méthode
        $method = camel_case($this->argument('name'));
        $method = PhpMethod::create($method);
        $method->setStatic(true);

        // Gestion du body
        $body = file_get_contents(__DIR__ . '/stubs/table.stub');
        $method->setBody($body);

        // block de commentaire
        $dockblock = new Docblock();
        $dockblock->appendTag(TagFactory::create('name', 'Artisan'));
        $dockblock->appendTag(TagFactory::create('see', 'php artisan ffmake:table'));
        $dockblock->appendTag(TagFactory::create('generated', Carbon::now()));
        $method->setDocblock($dockblock);


        // Récupération du controller à mettre à jour
        $controller = ucfirst(camel_case($controller . '_controller'));
        $controller  = new \ReflectionClass('App\\Http\\Controllers\\'.$controller);

        $class = PhpClass::fromReflection($controller)->setMethod($method);
        $class->setParentClassName('Controller');// fix la gestion des namespaec pour la parent class

        // Génration du code
        $generator = new CodeGenerator();
        $class = '<?php ' . $generator->generate($class);


        // inscription du code dans la classe
        file_put_contents($controller->getFileName(), $class);

        $this->info('Action generated dans le fichier : ' . $controller->getFileName());
    }
}
