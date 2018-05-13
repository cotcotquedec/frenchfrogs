<?php namespace FrenchFrogs\Maker;

use Roave\BetterReflection\Reflection\ReflectionMethod;

/**
 *
 *
 * Class Method
 * @package FrenchFrogs\Maker
 */
class Method
{
    use Docblock;
    use Modifier;

    /**
     * @var string
     */
    protected $body;


    /**
     * @var string
     */
    protected $name;

    /**
     *
     * @var array
     */
    protected $parameters = [];

    /**
     * Getter for $parameters
     *
     * @return Parameter[]
     */
    public function getParameters()
    {
        return $this->parameters;
    }

    /**
     * Add parameter
     *
     * @param mixed $name
     * @param bool $mandatory
     * @param null $default
     * @param null $type
     * @return $this
     */
    public function addParameter($name, $default = Maker::NO_VALUE, $type = null)
    {
        $parameter = $name instanceof Parameter ? $name : new Parameter($name, $default, $type);
        $this->parameters[$parameter->getName()] = $parameter;
        return $this;
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
     * Method constructor.
     * @param $name
     */
    public function __construct($name, $params = [], $body = '')
    {
        $this->setName($name);
        $this->enablePublic();// par default on set a public
        $this->setBody($body);
    }

    /**
     *
     *
     * @param ReflectionMethod $reflection
     * @return Method
     */
    static public function fromReflection(ReflectionMethod $reflection)
    {
        // gestion du type
//       $type = implode('|', $reflection->getDocBlockTypeStrings());
//

        // construction
        $method = new static($reflection->getName(), [], $reflection->getBodyCode());

        // docblock
        if ($reflection->getDocComment()) {
            $factory  = \phpDocumentor\Reflection\DocBlockFactory::createInstance();
            $docblock = $factory->create($reflection->getDocComment());
            empty($docblock->getSummary()) || $method->setSummary($docblock->getSummary());
            empty($docblock->getDescription()) || $method->setDescription($docblock->getDescription());


            if ($reflection->getDocBlockReturnTypes()) {
                foreach ($reflection->getDocBlockReturnTypes() as $type) {

                    switch (get_class($type)) {
                        case 'phpDocumentor\Reflection\Types\Boolean' :
                            $type = 'bool';
                            break;
                        case 'phpDocumentor\Reflection\Types\Object_' :
                            $type = $type->getFqsen()->getName();
                            break;
                        case  'phpDocumentor\Reflection\Types\Mixed' :
                            $type = 'mixed';
                            break;
                        case 'phpDocumentor\Reflection\Types\Void_':
                            $type = 'void';
                            break;
                        case 'phpDocumentor\Reflection\Types\String_':
                            $type = 'string';
                            break;
                        case 'phpDocumentor\Reflection\Types\Static_':
                            $type = 'static';
                            break;
                        case 'phpDocumentor\Reflection\Types\Array_':
                            $type = 'array';
                            break;
                        default :
                            throw new \Exception('Type pas encore pris en compte : ' . get_class($type));
                    }

                    $method->addTag('return', $type);
                }
            }
        }

        // gestion des modifiers
        $reflection->isPrivate() ? $method->enablePrivate() : $method->disablePrivate();
        $reflection->isProtected() ? $method->enableProtected() : $method->disabledProtected();
        $reflection->isPublic() ? $method->enablePublic() : $method->disablePublic();
        $reflection->isStatic() ? $method->enableStatic() : $method->disableStatic();
        $reflection->isFinal() ? $method->enableFinal() : $method->disableFinal();


        foreach ($reflection->getParameters() as $parameter) {
            $method->addParameter(Parameter::fromReflection($parameter));
        }

        return $method;
    }

    /**
     * Setter for $body
     *
     * @param $body
     * @return $this
     */
    public function setBody($body)
    {
        $this->body = strval($body);
        return $this;
    }

    /**
     * Ajout de contenu au body
     *
     * @param $content
     * @return $this
     */
    public function appendBody($content, $endline = PHP_EOL)
    {
        $this->body .= $content . PHP_EOL;
        return $this;
    }

    /**
     * Getter for $body
     *
     * @return string
     */
    public function getBody()
    {
        return $this->body;
    }
}
