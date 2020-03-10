<?php namespace FrenchFrogs\Table\Renderer;

use FrenchFrogs\Table\Column;

class AdminLTE extends Bootstrap
{

    /**
     * @param \FrenchFrogs\Table\Table\Table $table
     * @return mixed|string
     * @throws \Exception
     */
    public function table(\FrenchFrogs\Table\Table\Table $table)
    {

        /** @var $table \FrenchFrogs\Table\Table\Bootstrap */
        $html = parent::table($table);

        return $html;
    }

}
