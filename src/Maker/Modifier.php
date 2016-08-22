<?php namespace FrenchFrogs\Maker;


trait Modifier
{


    /**
     * If is private
     *
     * @var bool
     */
    protected $is_private = false;

    /**
     * If is protected
     *
     * @var bool
     */
    protected $is_protected = false;

    /**
     * If is public
     *
     * @var bool
     */
    protected $is_public = false;

    /**
     * If is static
     *
     * @var bool
     */
    protected $is_static = false;

    /**
     * If is final
     *
     * @var bool
     */
    protected $is_final = false;

    /**
     * Set $is_private to TRUE
     *
     * @return $this
     */
    public function enableFinal()
    {
        $this->is_final = true;
        return $this;
    }

    /**
     * Set $is_private to FALSE
     *
     * @return $this
     */
    public function disableFinal()
    {
        $this->is_final = false;
        return $this;
    }


    /**
     * Getter for $is_final
     *
     * @return bool
     */
    public function isFinal()
    {
        return $this->is_final;
    }


    /**
     * Set $is_private to TRUE
     *
     * @return $this
     */
    public function enablePrivate()
    {
        $this->is_private = true;
        return $this;
    }

    /**
     * Set $is_private to FALSE
     *
     * @return $this
     */
    public function disablePrivate()
    {
        $this->is_private = false;
        return $this;
    }

    /**
     * Set $is_static to TRUE
     *
     * @return $this
     */
    public function enableStatic()
    {
        $this->is_static = true;
        return $this;
    }

    /**
     * Set $is_static to FALSE
     *
     * @return $this
     */
    public function disableStatic()
    {
        $this->is_static = false;
        return $this;
    }

    /**
     * Set $is_public to TRUE
     *
     * @return $this
     */
    public function enablePublic()
    {
        $this->is_public = true;
        return $this;
    }

    /**
     * Set $is_public to FALSE
     *
     * @return $this
     */
    public function disablePublic()
    {
        $this->is_public = false;
        return $this;
    }

    /**
     * Set $is_protected to TRUE
     *
     * @return $this
     */
    public function enableProtected()
    {
        $this->is_protected = true;
        return $this;
    }

    /**
     * Set $is_protected to FALSE
     *
     * @return $this
     */
    public function disabledProtected()
    {
        $this->is_protected = false;
        return $this;
    }


    /**
     * Getter for $is_private
     *
     * @return bool
     */
    public function isPrivate()
    {
        return $this->is_private;
    }


    /**
     * Getter for $is_static
     *
     * @return bool
     */
    public function isStatic()
    {
        return $this->is_static;
    }

    /**
     * Getter for $is_public
     *
     * @return bool
     */
    public function isPublic()
    {
        return $this->is_public;
    }

    /**
     * Getter for $is_protected
     *
     * @return bool
     */
    public function isProtected()
    {
        return $this->is_protected;
    }
}