<?php namespace Frenchfrogs\App\Console;

use Carbon\Carbon;
use Illuminate\Console\Command;

class ControllerCreateCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'ffmake:controller
                             {name : Nom du controller en minuscule}
                             {--no-route= : Si on ne créé pas une route pour le controller}
                             ';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = "Création d'un controller";

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
        // nom du controller
        $name = $this->argument('name');

        // creation du controller
        $class = ucfirst(camel_case($name . '_controller'));
        $this->call('make:controller', ['name' => $class]);

        // gestion de la route
        if (!$this->hasOption('--no-route')) {
            $this->info('Création de la route');

            $route = PHP_EOL . '// '.Carbon::now().' Création automatique de la route : ' . $name;
            $route .= PHP_EOL . sprintf("Route::controller('%s', '%s');", $name, $class);
            file_put_contents(app_path('Http/controllers.php'), $route ,FILE_APPEND);
        }
    }
}
