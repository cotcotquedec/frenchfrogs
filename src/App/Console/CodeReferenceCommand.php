<?php namespace FrenchFrogs\App\Console;

use FrenchFrogs\App\Models\Db\Reference;
use FrenchFrogs\Maker\Maker;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Collection;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Composer;

class CodeReferenceCommand extends \FrenchFrogs\App\Console\CodeCommand
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'code:ref {search? :  recherche de la collection à la quel ajouter une reference}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Gestion des references';


    /**
     * Liste des collection disponible
     *
     * @var Collection
     */
    protected $collections;


    /**
     * @var string
     */
    protected $collection;


    /**
     * @var string
     */
    protected $id;

    /**
     * @var string
     */
    protected $name;


    /**
     * @var Filesystem
     */
    protected $filesystem;

    /**
     * @var Composer
     */
    protected $composer;

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct(Filesystem $filesystem, Composer $composer)
    {

        $this->filesystem = $filesystem;
        $this->composer= $composer;

        // load collection
        $this->collections = Reference::distinct('collection')->pluck('collection');

        parent::__construct();
    }


    /**
     *  Recherche d'une classe
     *
     * @return string
     */
    public function searchCollection($search = null)
    {


        do {
            $collection = null;

            do {
                $searches = collect([]);

                // recherche de la classe
                empty($search) && $search = $this->ask('A quelle collection voulez-vous ajouter une entrée?', static::CHOICE_LIST);

                // matching
                $searches = $this->collections->reject(function ($collection) use ($search) {
                    return $search != static::CHOICE_LIST && strpos(strtoupper($collection), strtoupper($search)) === false;
                });

                if ($searches->isEmpty()) {
                    $this->warn('Nous n\'avons pas trouvé de collection contenant le mot : ' . $search);

                    // cas d'une nouvelle collection
                    if ($this->confirm('Voulez vous utiliser la collection : ' . $search)) {
                        return $search;
                    }
                }

                $search = null;

            } while ($searches->isEmpty());

            // on ajoute un choix de non choix pour recommencer le process
            $found = $searches->count() == 1;
            $searches->push(static::CHOICE_EMPTY);
            $searches = $searches->values();

            // Choix definitif de la classe a modifier
            $collection = $this->choice('Quel collection souhaitez vous utiliser?', $searches->all(), $found ? 0 : $searches->search(static::CHOICE_EMPTY));

            // on supprime le choix si on souhaite revoir la proposition
            if ($collection == static::CHOICE_EMPTY) {
                unset($collection);
                $this->warn('Definir une collection est obligatoire');
            }

        } while (empty($collection));


        return $collection;
    }


    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {

        // recuperaztion de la collection
        $this->collection = $this->searchCollection($this->argument('search'));


        // recuperationet liste des reference existantes
        $references = Reference::where('collection', $this->collection)->get(['reference_id', 'name']);


        if ($references->isNotEmpty()) {
            $this->info('Voici les références existantes pour la collection : ' . $this->collection);
            $this->table(['ID', 'NAME'], $references->toArray());
        } else {
            $this->info('Il n\'y pas encore de reference pour la collection : ' . $this->collection);
        }


        // ID
        $validator = \Validator::make([], ['filled|regex:#^[A-Z0-9_]{5,}$#']);
        $id = null;
        do {
            $id = $this->ask('Quel ID voulez vous donner a la référence ?');

            if ($validator->setData([$id])->fails()) {
                $this->error('Erreur : ' . $validator->messages()->first());
                $id = null;
            }
        } while(is_null($id));
        $this->id = $id;

        // NAME
        $validator = \Validator::make([], ['filled|min:3#']);
        $name = null;
        do {
            $name =$this->ask('Quel est le libellé de la Reference?');

            if ($validator->setData([$name])->fails()) {
                $this->error('Erreur : ' . $validator->messages()->first());
                $name = null;
            }
        } while(is_null($name));
        $this->name = $name;


        // Recapitulatif
        $this->table(['Champs', 'Valeur '], [
            ['reference_id', $id],
            ['name', $name],
            ['collection', $this->collection],
        ]);

        // ceration de la migration
        $this->migration();

        // RELOAD COMPOSER
        $this->composer->dumpAutoloads();

        // Lancement de la migration
        $this->call('migrate');

        // Reconstrcutiond e la base de reference
        $this->call('ref:build');

        // End
        $this-> info('Have fun');
    }



    /**
     *  Creation de la migration
     *
     * @return Maker
     */
    public function migration()
    {
        // Creation de la migration
        $this->info('Creation de la migration');

        $filename = 'create_reference_' . $this->id . '_' . \uuid()->hex;
        $path = $this->laravel->databasePath() . '/migrations';
        $filepath = $this->laravel['migration.creator']->create($filename, $path);

        // CLASS
        $class = Maker::findClass($filepath);

        // On charge la migration
        require_once $filepath;

        // Cratiuon de la migration
        $migration = Maker::load($class);
        $migration->addAlias('Migration', Migration::class);
        $migration->addAlias('Reference', \FrenchFrogs\App\Models\Reference::class);
        $migration->setParent(Migration::class);
        $migration->setSummary('Migration pour l\'ajout de la reference "' . $this->id .  '"');

        // METHOD
        $method = $migration->addMethod('up');
        $method->setBody(<<<EOL
        Reference::createDatabaseReference('{$this->id}', '{$this->name}', '{$this->collection}');
EOL
);
        $migration->write();
    }
}
