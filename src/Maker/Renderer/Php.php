<?php namespace FrenchFrogs\Maker\Renderer;
use FrenchFrogs\Maker\Docblock;
use FrenchFrogs\Maker\Maker;
use FrenchFrogs\Maker\Method;
use FrenchFrogs\Maker\Modifier;
use FrenchFrogs\Maker\Parameter;
use FrenchFrogs\Maker\Property;
use FrenchFrogs\Renderer\Renderer;
use phpDocumentor\Reflection\DocBlock\Serializer;

class Php extends Renderer
{
    /**
     *
     * Available renderer
     *
     * @var array
     */
    protected $renderers = [
        'maker',
        'maker_class',
        'property',
        'docblock',
        'modifier',
        'value',
        'method'
    ];

    /**
     * Export array as php 5.4
     *
     * @param $var
     * @param string $indent
     * @return string
     */
    static public function export($var, $indent="") {
        switch (gettype($var)) {
            case "string":
                return '"' . addcslashes($var, "\\\$\"\r\n\t\v\f") . '"';
            case "array":
                $indexed = array_keys($var) === range(0, count($var) - 1);
                $r = [];
                foreach ($var as $key => $value) {
                    $r[] = "$indent    "
                        . ($indexed ? "" : static::export($key) . " => ")
                        . static::export($value, "$indent    ");
                }
                return "[\n" . implode(",\n", $r) . "\n" . $indent . "]";
            case "boolean":
                return $var ? "TRUE" : "FALSE";
            default:
                return var_export($var, TRUE);
        }
    }


    /**
     * Rendu d'un maker
     *
     * @param Maker $maker
     * @return string
     */
    public function maker(Maker $maker)
    {

        $content = '';

        // CONSTANT
        foreach ($maker->getConstants() as $name => $value) {
            $content .= sprintf('const %s = %s;', $name, $this->render('value', $value)) . PHP_EOL;
        }

        if (!empty($name)) {
            $content .= str_repeat(PHP_EOL,2);
        }

        // PROPERTIES
        foreach($maker->getProperties() as $property) {
            $content .= $this->render('property', $property) . str_repeat(PHP_EOL,2);
        }

        // METHODS
        $methods = [];
        foreach ($maker->getMethods() as $method) {
            $methods[] = $this->render('method', $method);
        }
        $content .= implode(str_repeat(PHP_EOL, 2), $methods);

        // rendu globale
        $content = $this->render('maker_class', $maker, $content);

        return $content;
    }

    /**
     * Render for class
     *
     * @param Maker $maker
     * @param $body
     * @return string
     */
    public function maker_class(Maker $maker, $body)
    {
        $content = '';
        $reflection = $maker->getClass();

        // NAMESPACE
        if ($maker->hasNamespace()) {
            $content .= 'namespace ' . $maker->getNamespace() . ';' . PHP_EOL;
        }
        $content .= str_repeat(PHP_EOL, 2);

        // ALIASES
        if ($maker->hasAliases()) {
            foreach ($aliases = $maker->getAliases() as $alias => $class) {
                $content .= 'use ' . $class;
                if (collect(explode('\\', $class))->last() != $alias) {
                    $content .= ' as ' . $alias;
                }
                $content .= ';' . PHP_EOL;
            }
            $content .= str_repeat(PHP_EOL, 2);
        }

        // DOCBLOCK
        $content .= $this->render('docblock', $maker);

        // declaration
        $content .= 'class ' . $reflection->getShortName();

        // PARENT
        if ($parent = $maker->getParent()) {
            $content .= ' extends ' . $maker->findAliasName($parent);
        }

        // @todo Gestion des interfaces
//        $interface = $reflection->getImmediateInterfaces();

        $content .= PHP_EOL;
        $content .= '{' . PHP_EOL . "\t" . str_replace(PHP_EOL, PHP_EOL . "\t", trim($body)) . PHP_EOL . '}';

        return $content;
    }


    /**
     * Rendu du property
     *
     * @param Property $property
     * @return string
     */
    public function property(Property $property)
    {
        $content = '';

        // dockblock
        $content .= $this->render('docblock', $property);
        $content .= $this->render('modifier', $property);
        $content .= '$' . $property->getName();

        if ($property->hasDefault()) {
            $content .= ' = ' . $this->render('value', $property->getDefault());
        }

        $content .= ';' . PHP_EOL;

        return $content;
    }


    /**
     * Rendu d'une methode
     *
     * @param Method $method
     * @return string
     */
    public function method(Method $method)
    {

        $content = '';

        // parameters
        $parameters = [];
        foreach ($method->getParameters() as $parameter) {

            $type = $parameter->getType();
            $name = $parameter->getName();
            $method->addTagParam($name, $type);

            $p = '';

            if (!is_null($type)) {
                $p .=  $type . ' ';
            }

            $p .= '$' . $name;


            if ($parameter->hasDefault()) {
                $p .= ' = ' . $this->render('value', $parameter->getDefault());
            }

            $parameters[] = $p;
        }

        $content .= $this->render('docblock', $method);
        $content .= sprintf('%sfunction %s(%s)', $this->render('modifier', $method), $method->getName(), implode(', ', $parameters)) . PHP_EOL;
        $content .= '{' . PHP_EOL . "\t" . str_replace(PHP_EOL, PHP_EOL . "\t", $method->getBody()) . PHP_EOL . '}' . PHP_EOL;

        return $content;
    }


    /**
     *
     * @param $value
     * @return string
     */
    public function value($value)
    {
        if (is_string($value)) {
            $value = "'". $value ."'";
        } elseif (is_array($value)) {
            $value = count($value) == 0 ? '[]' : static::export($value);
        } elseif (is_bool($value)) {
            $value = $value ? 'true' : 'false';
        } elseif(is_null($value)) {
            $value = 'null';
        }

        return $value;
    }

    /**
     * Render modifier
     *
     * @param Modifier $modifier
     * @return Modifier
     */
    public function modifier($modifier)
    {
        // On verifie que le trait est bien présent
        $traits = class_uses($modifier);
        if (!array_search(Modifier::class, $traits)) {
            exc('la classe "' . get_class($modifier) . '" n\'utilise pas le trait "' . Modifier::class . '"');
        }

        $content = '';
        $content .= $modifier->isStatic() ? 'static ' : '';
        $content .= $modifier->isPublic() ? 'public ' : '';
        $content .= $modifier->isProtected() ? 'protected ' : '';
        $content .= $modifier->isPrivate() ? 'private ' : '';
        $content .= $modifier->isFinal() ? 'final ' : '';

        return $content;
    }


    /**
     * Generation du docblock
     *
     * @param Docblock $docblock
     * @return string
     */
    public function docblock($docblock)
    {
        // On verifie que le trait est bien présent
        $traits = class_uses($docblock);
        if (!array_search(Docblock::class, $traits)) {
            exc('la classe "' . get_class($docblock) . '" n\'utilise pas le trait "' . Docblock::class . '"');
        }

        $content = '/**' . PHP_EOL;
        $content .= '* ' . $docblock->getSummary() . PHP_EOL;
        $content .= '* ' . PHP_EOL;
        $content .= '* ' . str_replace(PHP_EOL, PHP_EOL . '* ', $docblock->getDescription()) . PHP_EOL;
        $content .= '* ' . PHP_EOL;

        foreach ($docblock->getTags() as $tag) {
            list($name, $value) = $tag;
            $content .= sprintf('* @%s %s', $name, $value) . PHP_EOL;
        }

        $content .= '*/';

        // on fait proprement mais bon!
        // @todo passer sur la version 3 de phpdocumentor
        $factory = new \phpDocumentor\Reflection\DocBlock($content);
        $serializer =  new Serializer();
        $content = $serializer->getDocComment($factory) . PHP_EOL;

        return $content;
    }



}