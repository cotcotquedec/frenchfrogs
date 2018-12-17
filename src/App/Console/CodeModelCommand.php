<?php namespace FrenchFrogs\App\Console;

use FrenchFrogs\Laravel\Database\Eloquent\Model;
use FrenchFrogs\Maker\Maker;
use Illuminate\Database\Connection;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Composer;
use Illuminate\Support\Pluralizer;
use Models\Db\User;

class CodeModelCommand extends CodeCommand
{

    protected $relations = [
        'BelongsTo' => 'BelongsTo',
        'HasMany' => 'HasMany',
        'HasOne' => 'HasOne',
    ];


    /**
     * @var Maker
     */
    protected $maker;


    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'code:model {name? : Nom de la table} {--connection=}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Génération d\'un model';


    /**
     * @var Connection
     */
    protected $connection;


    /**
     *
     * Return a determined path for the file
     *
     * @param $class
     * @return mixed|string
     */
    protected function determineFileFromClass($class)
    {
        $file = preg_replace('#^' . addslashes(app()->getNamespace()) . '#', app_path('/'), $class);
        $file = str_replace('\\', '/', $file) . '.php';
        $file = str_replace('//', '/', $file);

        return $file;
    }


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

        // CONNECTION
        $this->connection = \DB::connection($this->option('connection') ?: \DB::getDefaultConnection());

        //PERMISSION
        do {
            if (empty($name)) {

                $tables = [];
                foreach ($this->connection->select('SHOW TABLES') as $row) {
                    $tables[] = current($row);
                }

                $name = $this->askWithCompletion('Quel est le nom de la table?', $tables);
            }
        } while (empty($name));

        // choix des nom de classe
        $choices = [];

        // determination du nom de la classe et du fichier de sortie
        $class = $file = null;

        // le cas où le fichier existe déja
        if ($class = Maker::findTable($name))  {
            $file = $this->determineFileFromClass($class);
        } else {

            // Construction du nom de la classe
            $class = $name;

            // cas du des underscore
            if (strpos($class, '_')) {

                // on separe les underscore et on ajoute des majuscule
                $class = [];
                foreach (explode('_', $name) as $item) {
                    $class[] = ucfirst($item);
                }

                // reconstruction du  nom de la class
                $class = implode('\\', $class);
            }

            // choix par default
            $class = str_replace('\\\\', '\\', Maker::getDbNamespace() . '\\' . ucfirst(camel_case($class)));
            $file = $this->determineFileFromClass($class);
        }

        // choix par defaut
        if (is_null($class) || is_null($file) || !$this->confirm(sprintf('Créer le modèle "%s" dans le ficher "%s"?', $class, $file), true)) {
            $class = $this->ask('Quelle est le nom de la classe?', $class);
            $file = $this->ask('Quelle est le nom du fichier?', $this->determineFileFromClass($class));
        }

        // Creation de la classe
        $maker = file_exists($file) ? Maker::load($class) : Maker::init($class, $file);

        // Attributiuon du maker a la commande
        $this->maker = $maker;
        $maker->setParent(Model::class);
        $maker->addProperty('table', $name)->enableProtected();
        $maker->addAlias('Model', Model::class);

        // gestion des containtes
        if ($this->confirm('Voulez vous générer relations?', true)) {
            $this->constraints($name);
        }

        if ($this->confirm('Voulez vous gerer les colonnes?', true)) {
            $this->columns($name);
        }

        // ecriture de la classe
        if ($this->confirm('Validez les changement?', true)) {
            $maker->write();
        }

        $this->info('Have fun!!');
    }


    /**
     * GEstion des colonnes
     *
     * @param $name
     */
    public function columns($name)
    {
        // recuperation du maker
        $maker = $this->maker;

        // CAST
        $casts = [];
        $dates = [];

        // TIMESTAMP
        $created = false;
        $updated = false;
        $deleted = false;

        // recuperation des colonnes
        $columns = $this->connection->select('SHOW COLUMNS FROM `'.$name.'`');

        foreach ($columns as $row) {

            $type = null;

            //PRIMARY KEY
            if ($row->Key == 'PRI') {

                // si la clé primaire est autre que "id" on l'inscrit
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
                    $maker->addProperty('keyType', 'string')->enableProtected();
                    $maker->addProperty('incrementing', false)->enablePublic();
                }
            }

            // UUID
            if ($row->Type == 'binary(16)') {
                $casts[$row->Field] = Model::BINARY16_UUID;
            }

            // boolean
            if ($row->Type == 'tinyint(1)' && preg_match('#(is|can|has)_.+#', $row->Field)) {
                $maker->addMethod(camel_case($row->Field))
                    ->setBody('return (bool) $this->' . $row->Field . ';')
                    ->addTag('return', 'bool')
                    ->addAnnotation('Getter for ' . $row->Field);
            }

            // JSON
            if (preg_match('#^json$#', $row->Type)) {
                $casts[$row->Field] = 'json';
                $type = 'array';
            }

            // TIMESTAMP
            if (in_array($row->Type, ['timestamp'])) {

                $type = '\\Carbon\\Carbon';
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
        (!$created || !$updated) && $maker->addProperty('timestamps')->enablePublic()->setDefault(false);

        // cas du soft deleted
        if ($deleted) {
            $maker->addAlias('SoftDeletes', SoftDeletes::class);
            $maker->addTrait(SoftDeletes::class);
        }

        count($casts) && $maker->addProperty('casts', $casts)->enableProtected();
        count($dates) && $maker->addProperty('dates', $dates)->enableProtected();
    }


    /**
     * @todo
     */
    public function constraints($table)
    {
        // Recuperation du maker
        $maker = $this->maker;

        // Constrainte

        $constraints = $this->connection
                            ->select("SELECT
                                   table_name,
                                   column_name,
                                   referenced_table_name,
                                   referenced_column_name
                                 FROM
                                   information_schema.key_column_usage
                                 WHERE
                                   table_schema = ? AND
                                   (table_name = ? OR referenced_table_name = ?)
                                   AND referenced_table_name IS NOT NULL", [$this->connection->getConfig('database'), $table, $table]);



        $configuration = collect([]);

        // Essaie de definir le resultats
        foreach ($constraints as $constraint) {

            $config = [];

            // cas d'une liaison externe
            if ($constraint->table_name == $table) {

                $config['id'] = str_random(3);

                    // Recuperation du nom de la table
                $config['class'] = Maker::findTable($constraint->referenced_table_name);

                if (empty($config['class'])) {
                    $this->table(['table_name', 'column_name', 'referenced_table_name', 'referenced_column_name'], [(array) $constraint]);
                    $this->warn('Le model pour la table : ' . $constraint->table_name . ' n\'a pas été trouvé!');
                    continue;
                }

                $config['type'] = 'BelongsTo';

                $name = $constraint->column_name;
                $name = collect(explode('_', $name));
                // on depop l'id
                $name->pop();
                // on prend la parti precedente l'id pour definir le nom de la liaison
                $name = $name->pop();

                $config['name'] = Pluralizer::singular($name);
                $config['from'] = $constraint->column_name;
                $config['to'] = $constraint->referenced_column_name;
                $config['exists'] = $maker->hasMethod($config['name']) ? '*' : '';

            } else {

                $config['id'] = str_random(3);

                // Recuperation du nom de la table
                $config['class'] = Maker::findTable($constraint->table_name);

                if (empty($config['class'])) {
                    $this->table(['table_name', 'column_name', 'referenced_table_name', 'referenced_column_name'], []);
                    $this->warn('Le model pour la table : ' . $constraint->table_name . ' n\'a pas été trouvé!');
                    continue;
                }

                $config['type'] = 'HasMany';

                $name = $constraint->table_name;
                $name = collect(explode('_', $name));

                // on prend la parti precedente l'id pour definir le nom de la liaison
                $name = $name->pop();

                $config['name'] = Pluralizer::plural($name);
                $config['from'] = $constraint->column_name;
                $config['to'] = $constraint->referenced_column_name;
                $config['exists'] = $maker->hasMethod($config['name']) ? '*' : '';
            }

            $configuration->put($config['id'], $config);
        }

        // Proposition du resultat
        $this->table(['ID', 'Classe', 'Type', 'Name', 'From', 'To', 'Exists'], $configuration->toArray());


        // Validation
        while (!$this->confirm('Voulez vous appliquez les contraintes telle quelle?', true)) {

            $choice = $this->choice('Laquel souhaitez vous modifier', $configuration->pluck('name', 'id')->toArray());

            if (!$configuration->has($choice)) {
                $this->warn('L\'entrée ' . $choice . ' n\'existe pas!');
            }

            // recuperation de l'entrée
            $config = $configuration->get($choice);

            // réécriture
            $config['type'] = $this->choice('Quel est le type de la relation?', $this->relations , $config['type']);
            $config['name'] = $this->ask('Quel est le nom de la relation', $config['name']);
            $configuration->put($choice, $config);

            // Proposition du resultat
            $this->table(['ID', 'Classe', 'Type', 'Name', 'From', 'To', 'Exists'], $configuration->toArray());
        }

        // Generation des function existantes
        $exists = $this->confirm('Regénérer les fonctions existantes?', true);

        foreach($configuration as $config) {

            if (!$exists && $config['exists']) {
                continue;
            }

            $class = null;

            switch($config['type']) {
                case 'BelongsTo' :
                    // ajout de l'alias du nom de la classe
                    $maker->addAlias(substr($config['class'], strrpos($config['class'], '\\') + 1), $config['class']);
                    $class = $maker->findAliasName($config['class']);

                    // Création
                    $maker->addTagPropertyRead($config['name'],$class);
                    $maker->addMethod($config['name'])
                        ->addTag('return', BelongsTo::class)
                        ->setBody('return $this->belongsTo(' . $class . '::class, "' . $config ['from'] . '", "' . $config['to'] . '");');
                    break;
                case 'HasMany' :

                    // ajout de l'alias du nom de la classe
                    $maker->addAlias(substr($config['class'], strrpos($config['class'], '\\') + 1), $config['class']);
                    $class = $maker->findAliasName($config['class']);

                    // Nom de la liaision
                    $name = Pluralizer::plural($config['name']);
                    $maker->addTagPropertyRead($name, Collection::class . '|' . $class . '[]');
                    $maker->addMethod($name)
                        ->addTag('return', HasMany::class)
                        ->setBody('return $this->hasMany(' . $class . '::class, "' . $config['from'] . '", "' . $config['to']  . '");');
                    break;

                default :
                    throw new \Exception('Config non connu');
            }
        }


        return $this;
    }
}
