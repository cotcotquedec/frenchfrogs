<?php namespace FrenchFrogs\Panel\Action;

use FrenchFrogs\Core;
use FrenchFrogs\Html\Element;

class ButtonMinimize extends Action
{
    /**
     * @return string
     */
    public function render()
    {
        $render = '';
        try {
            $render = $this->getRenderer()->render('buttonMinimize', $this);
        } catch(\Exception $e){
            dd($e->getMessage());
        }

        return $render;
    }


}