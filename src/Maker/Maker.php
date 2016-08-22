<?php namespace FrenchFrogs\Maker;

use BetterReflection\Reflection\ReflectionClass;
use FrenchFrogs\Core\Renderer;

class Maker
{
    use Renderer;
    use Docblock;

    const NO_VALUE = '__null__';

    /**
     * Fichier de sortie
     *
     * @var
     */
    protected $filename;

    /**
     * Properties to add or modify
     *
     * @var array
     */
    protected $properties = [];

    /**
     * Methods to add or modify
     *
     * @var array
     */
    protected $methods = [];

    /**
     * @var ReflectionClass
     */
    protected $class;

    /**
     * Constant de la class
     *
     * @var array
     */
    protected $constants = [];

    /**
     * @var string
     */
    protected $namespace;

    /**
     * @var string
     */
    protected $parent;

    /**
     * Alias declaration
     *
     * @var array
     */
    protected $aliases = [];


    /**
     * Getter for $constants
     *
     * @return array
     */
    public function getConstants()
    {
        return $this->constants;
    }

    /**
     * Setter for $costants
     *
     * @param array $constants
     */
    public function setConstants(array $constants)
    {
        $this->constants = $constants;
    }

    /**
     * add constant to $constant
     *
     * @param $name
     * @param $value
     * @return $this
     */
    public function addConstant($name, $value)
    {
        $this->constants[$name] = $value;
        return $this;
    }

    /**
     * Clear $constants
     *
     * @return $this
     */
    public function clearConstants()
    {
        $this->constants = [];
        return $this;
    }

    /**
     * Unset a constant
     *
     * @param $name
     * @return $this
     */
    public function removeConstants($name)
    {
        unset($this->constants[$name]);
        return $this;
    }

    /**
     * Return TRUE if $name constant is set
     *
     * @param $name
     * @return bool
     */
    public function hasConstant($name)
    {
        return isset($this->constants[$name]);
    }

    /**
     * Getter for $methods
     *
     * @return array
     */
    public function getMethods()
    {
        return $this->methods;
    }

    /**
     * Setter for $aliases
     *
     * @param $aliases
     * @return $this
     */
    public function setAliases($aliases)
    {
        $this->aliases = $aliases;
        return $this;
    }

    /**
     * Getter for $aliases
     *
     * @return array
     */
    public function getAliases()
    {
        return $this->aliases;
    }

    /**
     * Add alias to $aliases container
     *
     * @param $alias
     */
    public function addAlias($alias, $class)
    {
        $this->aliases[$alias] = $class;
    }

    /**
     * Clear all aliases
     *
     * @return $this
     */
    public function clearAliases()
    {
        $this->aliases = [];
        return $this;
    }

    /**
     * Return TRUE si au moins 1 alias est présent
     *
     * @return bool
     */
    public function hasAliases()
    {
        return !empty($this->aliases);
    }

    /**
     * find the good clas for class name
     * @param $class
     */
    public function findAliasName($class)
    {
        // initialisation
        $name = $class;

        // cas du namespace
        if($this->hasNamespace()) {
            $name = preg_replace('#^'.str_replace('\\', '\\\\', $this->getNamespace()).'\\\\#', '', $name);
        }

        // cas des alias
        foreach ($this->getAliases() as $alias => $c) {
            $a = preg_replace('#^'.str_replace('\\', '\\\\', $c).'#', $alias, $name);
            $name = strlen($a) < strlen($name) ? $a : $name;
        }

        return $name == $class ?  '\\' . $class : $name;
    }

    /**
     * Getter for $parent
     *
     * @return string
     */
    public function getParent()
    {
        return $this->parent;
    }

    /**
     * Setter for $parent
     *
     * @param $parent
     * @return $this
     */
    public function setParent($parent)
    {
        $this->parent = strval($parent);
        return $this;
    }

    /**
     * Return TRUE if a parent is set
     *
     * @return bool
     */
    public function hasParent()
    {
        return !empty($this->parent);
    }

    /**
     * Getter for $namespace
     *
     * @return string
     */
    public function getNamespace()
    {
        return $this->namespace;
    }

    /**
     * Setter for $namespace
     *
     * @param $namespace
     * @return $this
     */
    public function setNamespace($namespace)
    {
        $this->namespace = strval($namespace);
        return $this;
    }

    /**
     * Return TRUE if a namespace is present
     *
     * @return bool
     */
    public function hasNamespace()
    {
        return !empty($this->namespace);
    }


    /**
     * Setter pour la class
     *
     * @param $class
     * @return $this
     */
    public function setClass($class)
    {
        $this->class = $class;
        $this->reflect();
        return $this;
    }


    /**
     * Setter for $filename
     *
     * @param $filename
     * @return $this
     */
    public function setFilename($filename)
    {
        $this->filename = strval($filename);
        return $this;
    }

    /**
     * Getter for $filename
     *
     * @return mixed
     */
    public function getFilename()
    {
        return $this->filename;
    }

    /**
     * Analyse $class for extract self content
     *
     * @return $this
     */
    protected function reflect()
    {
        $reflection = $this->getClass();

        // docblock
        $docblock = new \phpDocumentor\Reflection\DocBlock($reflection->getDocComment());
        $this->setSummary($docblock->getShortDescription());
        $this->setDescription($docblock->getLongDescription());

        // NAMESPACE
        $namespace = $reflection->getNamespaceName();
        if (!empty($namespace)) {
            $this->setNamespace($namespace);
        }

        // ALIAS
        $file = $reflection->getFileName();
        $this->setFilename($file);

        // lecture du fichier
        if (file_exists($file)) {
            $file = file($file);

            //Première lignes du fichier
            foreach (array_slice($file, 0, $reflection->getStartLine()) as $line) {

                // analyse de partern d'alias
                if (preg_match('#use (?<class>[^\s^;]+)(\s+as\s+(?<alias>[^\s^;]+))?\s*;?#', $line, $match)) {
                    $class = $match['class'];
                    $alias = empty($match['alias']) ? collect(explode('\\', $class))->last() : $match['alias'];
                    $this->addAlias($alias, $class);
                }
            }
        }

        // PARENT CLASS
        $parent = $reflection->getParentClass();
        if (!empty($parent)) {
            $this->setParent($parent->getName());
        }

        // CONSTANT
        $this->setConstants($reflection->getConstants());

        // PROPERTIES
        foreach($reflection->getProperties() as $property) {
            $this->addProperty(Property::fromReflection($property));
        }

        // METHODS
        foreach ($reflection->getImmediateMethods() as $method) {
            $this->addMethod(Method::fromReflection($method));
        }

        return $this;
    }

    /**
     * Getter pour la class
     *
     * @return ReflectionClass
     */
    public function getClass()
    {
        return $this->class;
    }

    /**
     * Add a property
     *
     * @param $name
     * @param bool $mandatory
     * @param null $default
     * @param null $type
     * @return Property
     */
    public function addProperty($name, $default = Maker::NO_VALUE, $type = null)
    {
        $property = $name instanceof Property ?  $name : new Property($name, $default, $type);

        $this->properties[$property->getName()] = $property;
        return $this->properties[$property->getName()];
    }

    /**
     * @param $name
     * @param array $params @todo!!!!
     * @return Method
     */
    public function addMethod($name, $params = [])
    {
        $method = $name instanceof Method ?  $name : new Method($name, $params);
        return $this->methods[$method->getName()] = $method;
    }

    /**
     * Getter for $properties
     *
     * @return array
     */
    public function getProperties()
    {
        return $this->properties;
    }

    /**
     * Setter for $properties
     *
     * @param array $properties
     * @return $this
     */
    public function setProperties(array $properties)
    {
        $this->properties = $properties;
        return $this;
    }

    /**
     * Factory depuis une class existante
     *
     * @param $class
     */
    static public function load($class)
    {
        $instance = new static();
        $instance->setClass(ReflectionClass::createFromName($class));
        return $instance;
    }

    /**
     * Maker constructor.
     */
    public function __construct()
    {
        // CONFIGURATION
        if (!$this->hasRenderer()) {
            $class = configurator()->get('maker.renderer.class');
            $this->setRenderer(new $class);
        }
    }


    /**
     * Creation d'un fichier pour inititaliser une class
     *
     * @param $class
     * @param $file
     */
    static public function init($class, $file)
    {

        if (file_exists($file)) {
            exc('Impossible de créer la classe "'.$class.'", Le fichier existe deja : ' . $file );
        }

        file_put_contents($file, sprintf('<?php class %s {}', $class));
        require_once $file;
        return static::load($class);
    }

    /**
     * Render!
     *
     * @return mixed|string
     */
    public function render()
    {
        $render = '';
        try {
            $render = $this->getRenderer()->render('maker', $this);
        } catch(\Exception $e){
            dd($e->getMessage());//@todo find a good way to warn the developper
        }

        return $render;
    }

    /**
     * Ecrit dans le fichier le contenu
     *
     * @return $this
     */
    public function write()
    {
        file_put_contents($this->getFilename(), '<?php ' . $this->render(), LOCK_EX);
        return $this;
    }
}