<?php namespace FrenchFrogs\App\Console;

use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Composer;

class CodeControllerCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'code:controller
                             {name : Nom du controller en minuscule}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Création d\'un controller';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        // nom du controller
        $name = $this->argument('name');
        $name = str_replace('.', '\\_', $name);

        // creation du controller
        $class = ucfirst(camel_case($name . '_controller'));
        $this->call('make:controller', ['name' => $class]);
    }
}
