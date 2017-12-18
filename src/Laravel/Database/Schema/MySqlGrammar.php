<?php
/**
 * Created by PhpStorm.
 * User: jhouvion
 * Date: 18/12/17
 * Time: 12:00
 */

namespace FrenchFrogs\Laravel\Database\Schema;


class MySqlGrammar
{
    protected function typeUuid(Fluent $column)
    {
        return 'binary(16)';
    }
}