<?php namespace FrenchFrogs\Laravel\Database\Schema;


class MySqlGrammar extends \Illuminate\Database\Schema\Grammars\Grammar
{
    protected function typeUuid(Fluent $column)
    {
        return 'binary(16)';
    }
}