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
     * @param array ...$args
     * @return \Illuminate\Validation\Validator
     * @throws \Throwable
     */
    public function make(...$args)
    {
        return $this->getValidator()->make(...$args);
    }

    /**
     * @param array ...$args
     * @throws \Illuminate\Validation\ValidationException
     * @throws \Throwable
     */
    public function validate(...$args)
    {
        return $this->make(...$args)->validate();
    }

    /**
     * @param array ...$args
     * @return bool
     */
    public function fails(...$args)
    {
        return $this->make(...$args)->fails();
    }

    /**
     * @param array ...$args
     * @return array
     * @throws \Throwable
     */
    public function valid(...$args)
    {
        return $this->make(...$args)->valid();
    }


    /**
     * @return bool
     */
    public function errors()
    {
        return $this->getValidator()->errors();
    }
}
