<?php

namespace FrenchFrogs\Table\Renderer;

class AdminLTE extends Bootstrap
{
    public function table(\FrenchFrogs\Table\Table\Table $table)
    {

        /* @var $table \FrenchFrogs\Table\Table\Conquer */
        $html = parent::table($table);

        return $html;
    }
}
