<?php namespace FrenchFrogs\Panel\Action;

use FrenchFrogs\Core;
use FrenchFrogs\Html\Element;

class ButtonMinimize extends Action
{
    use Core\Remote;
    use Element\Button;

    /**
     * Constructror
     *
     * @param $name
     * @param string $label
     * @param array $attr
     */
    public function __construct()
    {
        $this->setRemoteId(ff()->get('modal.remoteId', $this->remoteId));
        $this->disableIconOnly();
    }


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