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
    public function __construct($name, $label = '', $attr = [])
    {
        $this->setAttributes($attr);
        $this->setName($name);
        $this->setLabel($label);
    }

    /**
     * @return string
     */
    public function __toString()
    {
        $render = '';
        try {
            $render = $this->validator('regex:/#([a-fA-F0-9]{3}){1,2}\b/')->getRenderer()->render('colorpicker', $this);
        } catch(\Exception $e){
            debugbar()->addThrowable($e);
        }
        return $render;
    }
}
