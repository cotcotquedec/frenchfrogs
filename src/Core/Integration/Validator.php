<?php namespace FrenchFrogs\Core\Integration;
use Illuminate\Validation\Concerns\ValidatesAttributes;
use Illuminate\Validation\Rule;

/**
 * Trait for validator polymorphisme
 *
 * Class Validator
 * @package FrenchFrogs\Core
 */
trait Validator
{


    private $_validator;

    /**
     * @return \FrenchFrogs\Core\Validator
     */
    public function getValidator()
    {
        // si le validtor n'existe pas, on le crée
        if (is_null($this->_validator)) {
            $this->_validator = new \FrenchFrogs\Core\Validator();
        }

        return $this->_validator;
    }


    /**
     * @param array ...$arg
     * @return \Illuminate\Validation\Validator
     * @throws \Throwable
     */
    public function make(...$arg)
    {
        return $this->getValidator()->make(...$arg);
    }

    /**
     * @param array ...$arg
     * @throws \Illuminate\Validation\ValidationException
     * @throws \Throwable
     */
    public function validate(...$arg)
    {
        return $this->make(...$arg)->validate();
    }

    /**
     * @param array ...$arg
     * @return bool
     */
    public function fails(...$arg)
    {
        return $this->make(...$arg)->fails();
    }

    /**
     * @param array ...$args
     * @return array
     * @throws \Throwable
     */
    public function valid(...$args)
    {
        return $this->make(...$arg)->valid();
    }


    /**
     * @return bool
     */
    public function errors()
    {
        return $this->getValidator()->errors();
    }
}