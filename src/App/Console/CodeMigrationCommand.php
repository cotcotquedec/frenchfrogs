<?php namespace FrenchFrogs\App\Console;

use FrenchFrogs\Maker\Maker;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Filesystem\Filesystem;
use FrenchFrogs\Laravel\Mail\Mailable;
use Illuminate\Support\Composer;

/**
 * Ajout d'un email
 *
 * Class CodeMailCommand
 * @package FrenchFrogs\App\Console
 */
class CodeMigrationCommand extends CodeCommand
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'code:migration {name? : name}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Création d\'une migration';

    /**
     * Execute the console command.
     *
     * @param Filesystem $filesystem
     * @param Composer $composer
     */
    public function handle(Filesystem $filesystem, Composer $composer)
    {
        // nom du controller
        $name = $this->argument('name');

        if (empty($name)) {
            do {
                if (empty($name)) {
                    $name = $this->ask('Quel est le nom de la migration?', 'migration_anonymous');
                }
            } while (empty($name));
        }

        // on s'assure de l'uncitie du lien
        $name .= '_' . str_random(6);

        $path = $this->laravel->databasePath() . '/migrations';
        $filepath = $this->laravel['migration.creator']->create($name, $path);

        // CLASS
        $class = Maker::findClass($filepath);

        // On charge la migration
        require_once $filepath;

        // Cratiuon de la migration
        $migration = Maker::load($class);
        $migration->addAlias('Migration', Migration::class);
        $migration->setParent(Migration::class);

        $summary = $this->ask('Description?', '');
        empty($summary) || $migration->setSummary($summary);

        // METHOD
        $migration->addMethod('up');





        $migration->write();


        $this->info('Migration généré : ');
        $this->warning($migration->getFilename());

        return;
    }
}
