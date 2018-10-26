<?php namespace FrenchFrogs\Form\Element;

use FrenchFrogs\Core;

class Submit extends Button
{

    protected $process;

    /**
     * Set un traitement
     *
     * @param $function
     * @return $this
     * @throws \Throwable
     */
    public function setProcess($function = null)
    {
        // Verifiation que l'on a bien une fonction
        throw_if(!is_callable($function) || is_string($function), 'Le process de traitement n\'est pas au bon format');

        $this->process = $function;

        return $this;
    }


    /**
     * Supprime le traitement
     *
     * @return $this
     */
    public function removeProcess()
    {
        unset($this->process);
        return $this;
    }


    /**
     *
     *
     * @param array ...$params
     * @return mixed
     */
    public function process(...$params)
    {
        return call_user_func_array($this->process, $params);
    }


    /**
     * Constructor
     *
     * @param $name
     * @param string $label
     * @param array $attr
     */
    public function __construct($name, $attr = [])
    {
        parent::__construct($name, $name, $attr);
        $this->addAttribute('type', 'submit');
    }

    /**
     * @return string
     */
    public function __toString()
    {

        $render = '';
        try {
            $render = $this->getRenderer()->render('submit', $this);
        } catch (\Exception $e) {
            debugbar()->addThrowable($e);
        }

        return $render;

    }
}
