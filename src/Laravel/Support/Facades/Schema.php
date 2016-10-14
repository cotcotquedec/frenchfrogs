<?php

namespace FrenchFrogs\Laravel\Support\Facades;

use FrenchFrogs\Laravel\Database\Schema\MySqlBuilder;
use FrenchFrogs\Laravel\Database\Schema\MySqlGrammar;

class Schema extends \Illuminate\Support\Facades\Schema
{
    /**
     * Get a schema builder instance for the default connection.
     *
     * @return \Illuminate\Database\Schema\Builder
     */
    protected static function getFacadeAccessor()
    {
        return static::getSchemaBuilder(static::$app['db']->connection());
    }

    /**
     * Get a schema builder instance for the connection.
     *
     * @param $connection \Illuminate\Database\Connection
     *
     * @return \FrenchFrogs\Laravel\Database\Schema\MysqlBuilder
     */
    public static function getSchemaBuilder($connection)
    {
        if (get_class($connection) === 'Illuminate\Database\MySqlConnection') {
            /* @var \Illuminate\Database\MySqlConnection $connection */
            $MySqlGrammar = $connection->withTablePrefix(new MySqlGrammar());
            $connection->setSchemaGrammar($MySqlGrammar);

            return new MySqlBuilder($connection);
        }

        return $connection->getSchemaBuilder();
    }
}
