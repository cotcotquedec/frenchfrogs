<?php namespace FrenchFrogs\App\Console;

use FrenchFrogs\Laravel\Database\Eloquent\Model;
use FrenchFrogs\Maker\Maker;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Composer;

class CodeModelCommand extends CodeCommand
{

    protected $namespace = '\\Models\\Db\\';
    protected $directory = 'app/Models/Db/';


    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'code:model {name? : Nom de la table}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Génération d\'un model';

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

        //PERMISSION
        do {
            if (empty($name)) {

                $tables = [];
                foreach (\DB::select('SHOW TABLES') as $row) {
                    $tables[] = current($row);
                }

                $name = $this->askWithCompletion('Quel est le nom de la table?', $tables);
            }
        } while (empty($name));


        // nom de la class
        $class = $this->namespace . ucfirst(camel_case($name));
        $class = $this->ask('Quelle est le nom de la classe?', $class);

        $file = ($this->directory) . ucfirst(camel_case($name)) . '.php';
        $file = $this->ask('Quelle est le nom du fichier?', $file);

        // recuperation des colonnes
        $columns = \DB::select('SHOW COLUMNS FROM ' . $name);

        // Creation de la classe
        $maker = file_exists($file) ? Maker::load($class) : Maker::init($class, $file);
        $maker->setParent(Model::class);
        $maker->addProperty('table', $name)->enableProtected();
        $maker->addAlias('Model', Model::class);

        // CAST
        $casts = [];
        $dates = [];

        // TIMESTAMP
        $created = false;
        $updated = false;
        $deleted = false;

        foreach ($columns as $row) {

            $type = null;

            //PRIMARY KEY
            if ($row->Key == 'PRI') {

                // si la clé primaiure est autre que "id" on l'inscrit
                if ($row->Field != 'id') {
                    $maker->addProperty('primaryKey', $row->Field)->enableProtected();
                }

                // cas d'une primary int mais pas incrémenté
                if (preg_match('#^int\(\d+\)$#', $row->Type) && $row->Extra != 'auto_increment') {
                    $maker->addProperty('incrementing', false)->enablePublic();
                }

                // cas d'un uuid
                if ($row->Type == 'binary(16)' && $row->Extra != 'auto_increment') {
                    $maker->addProperty('keyType', Model::BINARY16_UUID)->enablePublic();
                }

                // cas d'un id string
                if (preg_match('#^varchar\(\d+\)$#', $row->Type) && $row->Extra != 'auto_increment') {
                    $maker->addProperty('incrementing', false)->enablePublic();
                }
            }

            // UUID
            if ($row->Type == 'binary(16)') {
                $casts[$row->Field] = Model::BINARY16_UUID;
            }

            // JSON
            if (preg_match('#^json$#', $row->Type)) {
                $casts[$row->Field] = 'json';
                $type = 'array';
            }

            // TIMESTAMP
            if (preg_match('#_at$#', $row->Field)) {
                $maker->addAlias('Carbon', '\\Carbon\\Carbon');
                $type = 'Carbon';

                $dates[] = $row->Field;

                if ($row->Field == 'created_at') {
                    $created = true;
                }

                if ($row->Field == 'updated_at') {
                    $updated = true;
                }

                if ($row->Field == 'deleted_at') {
                    $deleted = true;
                }
            }

            $maker->addTagProperty('$' . $row->Field, $type);
        }

        // timemstamps
        !$created || !$updated && $maker->addProperty('timestamps')->enablePublic()->setDefault(false);

        // cas du soft deleted
        if ($deleted) {
            $maker->addAlias('SoftDeletes', SoftDeletes::class);
            $maker->addTrait(SoftDeletes::class);
        }

        count($casts) && $maker->addProperty('casts', $casts)->enableProtected();
        count($dates) && $maker->addProperty('dates', $dates)->enableProtected();

        // ecriture de la classe
        $maker->write();
        $this->info('Have fun!!');
    }
}