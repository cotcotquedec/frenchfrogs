<?php

namespace FrenchFrogs\Core;

/**
 * Trait for render polymorphisme.
 *
 * Class Renderer
 */
trait Renderer
{
    /**
     * Renderer container.
     *
     * @var \FrenchFrogs\Renderer\Renderer
     */
    protected $renderer;

    /**
     * Getter.
     *
     * @return \FrenchFrogs\Renderer\Renderer
     */
    public function getRenderer()
    {
        return $this->renderer;
    }

    /**
     * Setter.
     *
     * @param \FrenchFrogs\Renderer\Renderer $renderer
     *
     * @return $this
     */
    public function setRenderer(\FrenchFrogs\Renderer\Renderer $renderer)
    {
        $this->renderer = $renderer;

        return $this;
    }

    /**
     * Return TRUE if a renderer is set.
     *
     * @return bool
     */
    public function hasRenderer()
    {
        return isset($this->renderer);
    }

    /**
     * Shortcut to the main function of the model.
     *
     * @return string
     */
    public function render()
    {
        return strval($this);
    }
}
