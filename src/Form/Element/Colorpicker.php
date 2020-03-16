<?php namespace FrenchFrogs\Form\Element;

class Colorpicker extends Text
{

    /**
     * Constructror
     *
     * @param $name
     * @param string $label
     * @param array $attr
     */
    public function __construct($name, $label = '', $attr = [] )
    {
        $this->setAttributes($attr);
        $this->setName($name);
        $this->setLabel($label);
        $this->validator('regex:/^#(0-9a-f){6}$/');
    }

    /**
     * @return string
     */
    public function __toString()
    {
        $render = '';
        try {
            $render = $this->getRenderer()->render('colorpicker', $this);
        } catch(\Exception $e){
            debugbar()->addThrowable($e);
        }
        return $render;
    }
}
