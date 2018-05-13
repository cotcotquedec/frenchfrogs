<?php namespace FrenchFrogs\Maker;


use Roave\BetterReflection\Reflection\ReflectionParameter;
use phpDocumentor\Reflection\Types\Array_;
use phpDocumentor\Reflection\Types\Object_;

class Parameter
{

    /**
     * @var string
     */
    protected $name;

    /**
     * Default value
     *
     * @var mixed
     */
    protected $default = null;

    /**
     *
     * @var string
     */
    protected $type;


    /**
     * Parameter constructor.
     *
     *
     * @param $name
     * @param bool $mandatory
     * @param null $default
     * @param null $type
     */
    public function __construct($name, $default = Maker::NO_VALUE, $type = null)
    {
        $this->setName($name);
        $this->setDefault($default);

        if (!is_null($type)) {
            $this->setType($type);
        }
    }

    /**
     * Factory depuis une reflection
     *
     * @param ReflectionParameter $reflection
     * @return static
     */
    static public function fromReflection(ReflectionParameter $reflection)
    {

        // VALUE
        $value = $reflection->isDefaultValueAvailable() ? $reflection->getDefaultValue() : Maker::NO_VALUE;

        // TYPE
        $type = $reflection->getTypeHint();

        // analyse du type
        if (!is_null($type)) {
            if ($type instanceof Object_) {
                $type = strval($type->getFqsen());
            } elseif($type instanceof Array_) {
                $type = 'array';
            } else {
                exc('Impossible de determiner le type du paramètre : ' . $reflection->getName());
            }
        }

        // construction
        $parameter = new static($reflection->getName(), $value, $type);

        return $parameter;
    }

    /**
     * Setter for $type
     *
     * @param $type
     * @return $this
     */
    public function setType($type)
    {
        $this->type = $type;
        return $this;
    }

    /**
     * Getter for $type
     *
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Return TRUE is ype is set
     *
     * @return bool
     */
    public function hasType()
    {
        return isset($this->type);
    }

    /**
     * Unset $type
     *
     * @return $this
     */
    public function removeType()
    {
        unset($this->type);
        return $this;
    }



    /**
     * Setter for default
     *
     * @param $default
     * @return $this
     */
    public function setDefault($default)
    {
        $this->default = $default;
        return $this;
    }

    /**
     * Getter for default
     *
     * @return mixed
     */
    public function getDefault()
    {
        return $this->default;
    }

    /**
     * Getter for $name
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     *
     * Setter for $name
     *
     * @param $name
     * @return $this
     */
    public function setName($name)
    {
        $this->name = strval($name);
        return $this;
    }

    /**
     * Return TRUE if default valus is set
     *
     * @return bool
     */
    public function hasDefault()
    {
        return $this->default !== Maker::NO_VALUE;
    }
}