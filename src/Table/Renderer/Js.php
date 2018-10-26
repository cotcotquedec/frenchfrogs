<?php namespace FrenchFrogs\Table\Renderer;


use FrenchFrogs\Table\Table\Table;

class Js extends Remote
{

    function table(Table $table)
    {
        return strval(js('onload'));
    }
}