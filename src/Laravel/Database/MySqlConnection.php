<?php namespace FrenchFrogs\Laravel\Database;


use FrenchFrogs\Laravel\Database\Schema\Blueprint;


/**
 * Class MySqlConnection
 * @package FrenchFrogs\Laravel\Database
 */
class MySqlConnection extends \Illuminate\Database\MySqlConnection
{

    /**
     * Get a schema builder instance for the connection.
     *
     * @return \Illuminate\Database\Schema\MySqlBuilder
     */
    public function getSchemaBuilder()
    {
        // BUILDER
        $builder = parent::getSchemaBuilder($this);

        // OVERLOAD blueprint
        $builder->blueprintResolver( function ($table, $callback) {
            return new Blueprint($table, $callback);
        });

        return $builder;
    }
}