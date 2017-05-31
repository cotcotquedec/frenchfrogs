<?php namespace FrenchFrogs\App\Console;

use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Composer;

class AclTableCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'acl:table';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Création du fichier de migration pour la gestion des ACL';

    /**
     * The filesystem instance.
     *
     * @var \Illuminate\Filesystem\Filesystem
     */
    protected $files;
    /**
     * @var \Illuminate\Support\Composer
     */
    protected $composer;
    /**
     * Create a new session table command instance.
     *
     * @param  \Illuminate\Filesystem\Filesystem  $files
     * @param  \Illuminate\Support\Composer  $composer
     * @return void
     */
    public function __construct(Filesystem $files, Composer $composer)
    {
        parent::__construct();
        $this->files = $files;
        $this->composer = $composer;
    }
    /**
     * Execute the console command.
     *
     * @return void
     */
    public function fire()
    {
        $fullPath = $this->createTable();
        $this->files->put($fullPath, $this->files->get(__DIR__.'/stubs/tables/acl.stub'));
        $this->info('Migration created successfully!');
        $this->composer->dumpAutoloads();
    }

    /**
     * Create a base migration file for the session.
     *
     * @return string
     */
    protected function createTable()
    {
        $name = 'create_acl_table';
        $path = $this->laravel->databasePath().'/migrations';
        return $this->laravel['migration.creator']->create($name, $path);
    }
}
