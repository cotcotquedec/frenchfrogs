<?php namespace FrenchFrogs\App\Models;

use Cache;
use FrenchFrogs\Laravel\Database\Eloquent\Model;
use FrenchFrogs\Maker\Maker;

/**
 * Classe de gestion des références
 *
 * Class Reference
 * @package FrenchFrogs\App\Models
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
     * @var Model
     */
    protected $db;


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

        $this->db = Maker::getModelFromTableName('references');

        // generation des données
        $this->getData();
    }


    /**
     * constructor du singleton
     *
     * @return static
     */
    static function getInstance($collection)
    {

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
        if ($this->hasData()) {
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
                $data = $this->db
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
            $pairs[$row->reference_id] = $row->name;
        }

        return $pairs;
    }

    /**
     * Construction du fichier d'helper pour l'ide afin d'avoir l'autocompletion
     *
     */
    static public function build()
    {
        $file = storage_path('/../bootstrap/') . static::CLASS_NAME . '.php';

        // recuperation des données
        $constant = Maker::getModelFromTableName('references')->pluck('rid', 'rid')->toArray();

        // generate class
        $maker = Maker::load(static::CLASS_NAME);
        $maker->setConstants($constant);
        $maker->write();
    }

}