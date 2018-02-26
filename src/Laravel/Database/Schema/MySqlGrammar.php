<?php namespace FrenchFrogs\Laravel\Database\Schema;


use Illuminate\Support\Fluent;

class MySqlGrammar extends \Illuminate\Database\Schema\Grammars\MySqlGrammar
{
    protected function typeUuid(Fluent $column)
    {
        return 'binary(16)';
    }

    protected function typeMediumBlob(Fluent $column)
    {
        return 'mediumblob';
    }
}