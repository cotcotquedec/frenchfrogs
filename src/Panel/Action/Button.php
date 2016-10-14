<?php

namespace FrenchFrogs\Panel\Action;

use FrenchFrogs\Core;
use FrenchFrogs\Html\Element;

class Button extends Action
{
    use Core\Remote;
    use Element\Button;

    /**
     * Constructror.
     *
     * @param $name
     * @param string $label
     * @param array  $attr
     */
    public function __construct($name, $label = '', $attr = [])
    {
        $this->setRemoteId(configurator()->get('modal.remote.id', $this->remoteId));
        $this->setAttributes($attr);
        $this->setName($name);
        $this->setLabel($label);
        $this->setOptionAsDefault();
        $this->disableIconOnly();
    }

    /**
     * @return string
     */
    public function render()
    {
        $render = '';
        try {
            $render = $this->getRenderer()->render('button', $this);
        } catch (\Exception $e) {
            dd($e->getMessage());
        }

        return $render;
    }
}
