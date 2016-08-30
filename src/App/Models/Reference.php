<?php namespace FrenchFrogs\Models;

use Cache;
use FrenchFrogs\Maker\Maker;
use gossi\codegen\model\PhpClass;
use gossi\codegen\generator\CodeFileGenerator;

/**
 * Classe de gestion des références
 *
 * Class Reference
 * @package FrenchFrogs\Models
 */
class Reference
{

    /**
     * CPrefix utiliser pour le cache
     *
     */
    const CACHE_PREFIX = 'reference_';

    /**
     * Nom de la classe pour l'auto completion
     *
     */
    const CLASS_NAME = 'Ref';

    /**
     * Collection de reference
     *
     * @var
     */
    protected $collection;


    /**
     * Full data
     *
     * @var
     */
    protected $data;


    /**
     * Instances
     *
     * @var array
     */
    static protected $instances = [];


    /**
     * Constructor
     *
     * Reference constructor.
     * @param $collection
     */
    protected function __construct($collection)
    {
        $this->collection = $collection;

        // generation des données
        $this->getData();
    }


    /**
     * constructor du singleton
     *
     * @return static
     */
    static function getInstance($collection) {

        if (!array_key_exists($collection, static::$instances)) {
            self::$instances[$collection] = new static($collection);
        }

        return self::$instances[$collection];
    }

    /**
     * Return the cache index
     *
     * @return string
     */
    public function getCacheName()
    {
        return static::CACHE_PREFIX . $this->collection;
    }

    /**
     * Clear all cache foir the collection
     *
     * @return $this
     */
    public function clear()
    {
        // unset data
        if($this->hasData()){
            unset($this->data);
        }

        // unset cache
        Cache::forget($this->getCacheName());

        return $this;
    }


    /**
     * Return TRUE if $data is set
     *
     * @return bool
     */
    public function hasData()
    {
        return isset($this->data);
    }

    /**
     * Recuperation des données de la collection
     *
     * @param bool $force_refresh
     */
    public function getData()
    {
        // si pas de donnée on regénère
        if (!isset($this->data)) {

            // adresse du cache
            $cache = static::CACHE_PREFIX . $this->collection;

            // si pas les données en cache, on les génère
            if (!Cache::has($cache)) {
                $data = \query('reference')
                    ->whereNull('deleted_at')
                    ->where('collection', $this->collection)
                    ->orderBy('name')
                    ->get();
                Cache::forever($cache, $data);
            } else {
                $data = Cache::get($cache);
            }

            // inscription des data
            $this->data = $data;
        }

        return $this->data;
    }

    /**
     * Return $data as pair with id => name
     *
     * @return array
     */
    public function pairs()
    {
        $pairs = [];
        foreach ($this->data as $row) {
            $pairs[$row['reference_id']] = $row['name'];
        }

        return $pairs;
    }



    /**
     * Création d'une référence en base de donnée
     *
     * @param $id
     * @param $name
     * @param $collection
     * @param null $data
     * @return static
     */
    static public function createDatabaseReference($id, $name, $collection, $data = null )
    {
        return Db\Reference::create([
            'reference_id' => $id,
            'name' => $name,
            'collection' => $collection,
            'data' => json_encode($data)
        ]);
    }

    /**
     * Soft delete a reference
     *
     * @param $id
     * @throws \Exception
     */
    static public function removeDatabaseReference($id)
    {
        Db\Reference::find($id)->delete();
    }

    /**
     * Construction du fichier d'helper pour l'ide afin d'avoir l'autocompletion
     *
     */
    static public function build()
    {
        $file = storage_path('/../bootstrap/') . static::CLASS_NAME . '.php';

        // recuperation des données
        $constant = \FrenchFrogs\Models\Db\Reference::pluck('reference_id', 'reference_id')->toArray();

        // generate class
        $maker = Maker::load(static::CLASS_NAME);
        $maker->setConstants($constant);
        $maker->write();
    }

}