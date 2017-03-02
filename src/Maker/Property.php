<?php namespace FrenchFrogs\Maker;


use BetterReflection\Reflection\ReflectionProperty;

class Property
{
    use Modifier;
    use Docblock;

    /**
     * @var string
     */
    protected $name;

    /**
     * Default value
     *
     * @var mixed
     */
    protected $default;

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
    public function __construct($name, $default = Maker::NO_VALUE, $type = '')
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
     * @param ReflectionProperty $reflection
     * @return static
     */
    static public function fromReflection(ReflectionProperty $reflection)
    {
        // gestion du type
        $type = implode('|', $reflection->getDocBlockTypeStrings());

        // cas de la valeur null
        $value = $reflection->getDefaultValue();

        if (is_null($value)) {
            $value = Maker::NO_VALUE;

            $class = $reflection->getDeclaringClass();
            foreach ($class->getAst()->stmts as $stmt) {

                // si pas un attribut, on zap
                if (!$stmt instanceof \PhpParser\Node\Stmt\Property) {
                    continue;
                }

                foreach ($stmt->props as $prop) {
                    if ($prop instanceof \PhpParser\Node\Stmt\PropertyProperty) {

                        // lecture du fichier
                        $file = file($class->getFileName());

                        if (!empty($line = $file[$prop->getLine() - 1])) {
                            if (strpos($line, '=')) {
                                $value = null;
                            }
                        }
                    }
                }
            }
        }

        // construction
        $property = new static($reflection->getName(), $value, $type);

        // docblock
        $docblock = new \phpDocumentor\Reflection\DocBlock($reflection->getDocComment());
        $property->setSummary($docblock->getShortDescription());
        $property->setDescription($docblock->getLongDescription());

        // gestion des modifiers
        $reflection->isPrivate() ? $property->enablePrivate() : $property->disablePrivate();
        $reflection->isProtected() ? $property->enableProtected() : $property->disabledProtected();
        $reflection->isPublic() ? $property->enablePublic() : $property->disablePublic();
        $reflection->isStatic() ? $property->enableStatic() : $property->disableStatic();

        return $property;
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
     * Return TRUE if default valus is set
     *
     * @return bool
     */
    public function hasDefault()
    {
        return $this->default != Maker::NO_VALUE;
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
}