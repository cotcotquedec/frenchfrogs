<?php namespace FrenchFrogs\PCRE;


use Illuminate\Support\Collection;

class Result extends Collection
{


    /**
     * @var Collection
     */
    protected $references;

    /**
     * @return Collection
     */
    public function references()
    {
        return $this->references;
    }

    /**
     * Result constructor.
     * @param array $items
     */
    public function __construct($items = [])
    {
        parent::__construct($items);

        // Initialisation des references
        $this->references = collect();

        // Calcul des references
        $this->each(function($value, $key) {

            $explode = explode('__', $key);

            // Cas d'une reference identifié
            if (count($explode) == 2) {
                $this->put($explode[0], $value);
                $this->references()->put($explode[0], $explode[1]);
            }
        });
    }

}