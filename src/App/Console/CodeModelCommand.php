<?php namespace FrenchFrogs\App\Console;

use FrenchFrogs\Laravel\Database\Eloquent\Model;
use FrenchFrogs\Maker\Maker;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Composer;
use Illuminate\Support\Pluralizer;

class CodeModelCommand extends CodeCommand
{

    protected $namespace = '\\Models\\Db\\';
    protected $directory = 'app/Models/Db/';


    /**
     * @var Maker
     */
    protected $maker;


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


        // choix des nom de classe
        $choices = [];

        // cas du des underscore
        if (strpos($name, '_')) {

            // oin seprae les underscore et on ajoute des majuscule
            $break = [];
            foreach (explode('_', $name) as $item) {
                $break[] = ucfirst($item);
            }

            // reconstruction du  nom de la class
            $break = implode('\\', $break);
            $choices[] = $this->namespace . ucfirst(camel_case($break));
        }

        // ajout du choix par default
        $choices[] = $this->namespace . ucfirst(camel_case($name));
        $class = $this->choice('Quelle est le nom de la classe?', $choices, 0);

        $file = 'app/' . str_replace('\\', '/', $class) . '.php';
        $file = str_replace('//', '/', $file);
        $file = $this->ask('Quelle est le nom du fichier?', $file);

        // Creation de la classe
        $maker = file_exists($file) ? Maker::load($class) : Maker::init($class, $file);

        // Attributiuon du maker a la commande
        $this->maker = $maker;
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

        $this->constraints($name);

        // recuperation des colonnes
        $columns = \DB::select('SHOW COLUMNS FROM ' . $name);

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

            // boolean
            if ($row->Type == 'tinyint(1)' && preg_match('#(is|can|has)_.+#', $row->Field)) {
                if (!$maker->hasMethod(camel_case($row->Field))) {
                    $maker->addMethod(camel_case($row->Field))
                        ->setBody('return (bool) $this->' . $row->Field . ';')
                        ->addTag('return', 'bool')
                        ->addAnnotation('Getter for ' . $row->Field);
                }
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


    /**
     * @todo
     */
    public function constraints($table)
    {
        // Recuperation du maker
        $maker = $this->maker;

        // Constrainte
        $constraints = \DB::select("SELECT
                                   column_name,
                                   referenced_table_name,
                                   referenced_column_name
                                 FROM
                                   information_schema.key_column_usage
                                 WHERE
                                   table_schema = ? AND
                                   table_name = ?
                                   AND referenced_table_name IS NOT NULL", ['jobmaker', $table]);


        // on ajoute les alias généraux
        $maker->addAlias('Collection', Collection::class);
        $maker->addAlias('HasMany', HasMany::class);
        $maker->addAlias('HasOne', HasOne::class);

        foreach ($constraints as $constraint) {

            // ON valide que l'on veux bien mettre en place la constrainte
            if (!$this->confirm(sprintf('Faire une liaison pour le champ "%s" vers "%s.%s"', $constraint->column_name, $constraint->referenced_table_name , $constraint->referenced_column_name), true)) {
                continue;
            }

            //Definir le type de liaison
            $type = $this->choice('Quel type de liaison', ['OneToOne', 'OneToMany', static::CHOICE_NO_MORE], 1);

            // Recuperation du nom de la table
            $class =  Maker::findTable($constraint->referenced_table_name);

            // si on en trouve pas de model pour la table en question, on continue
            if (empty($class)) {
                $this->error('Nous n\'avons pas reussie a trouver la table : ' . $constraint->referenced_table_name);
                continue;
            }

            // ajout de l'alias du nom de la classe
            $maker->addAlias(substr($class, strrpos($class, '\\') + 1), $class);
            $class = $maker->findAliasName($class);

            switch ($type) {

                case 'OneToOne':

                    // Nom de la liaision
                    $name = $this->ask('Comment voulez vous nommer la relation?', camel_case($constraint->referenced_table_name));
                    $maker->addTagProperty($name, $class);
                    $maker->addMethod($name)
                        ->addTag('return', 'HasOne')
                        ->setBody('return $this->hasOne(' . $class . '::class, "' . $constraint->referenced_column_name . '", "' . $constraint->column_name . '");');
                    break;


                case 'OneToMany':

                    // Nom de la liaision
                    $name = Pluralizer::plural($constraint->referenced_table_name);
                    $name = $this->ask('Comment voulez vous nommer la relation?', camel_case($name));
                    $maker->addTagProperty($name, 'Collection');
                    $maker->addMethod($name)
                        ->addTag('return', 'HasMany')
                        ->setBody('return $this->hasMany('.$class.'::class, "' . $constraint->referenced_column_name . '", "' . $constraint->column_name . '");');
                    break;

                case static::CHOICE_NO_MORE :
                    continue;
                    break;
            }
        }
    }
}