<?php namespace FrenchFrogs\Maker;

use BetterReflection\Reflection\ReflectionClass;
use FrenchFrogs\Core\Renderer;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Filesystem\Filesystem;

class Maker
{
    use Renderer;
    use Docblock;

    const NAMESPACE_CONTROLLER = 'App\\Http\\Controllers\\';
    const NAMESPACE_DB = 'Models\\Db\\';

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
     * @var Property[]
     */
    protected $properties = [];

    /**
     * Methods to add or modify
     *
     * @var Method[]
     */
    protected $methods = [];


    /**
     * Traits
     *
     * @var array
     */
    protected $traits = [];


    /**
     * Interfaces
     *
     * @var array
     */
    protected $interfaces = [];


    /**
     * Class principale
     *
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
     * Getter for $traits
     *
     * @return array
     */
    public function getTraits()
    {
        return $this->traits;
    }

    /**
     * Setter for $costants
     *
     * @param array $traits
     */
    public function setTraits(array $traits)
    {
        $this->traits = $traits;
    }

    /**
     * add trait to $trait
     *
     * @param $name
     * @param $value
     * @return $this
     */
    public function addTrait($value)
    {
        $this->hasTrait($value) || $this->traits[] = $value;
        return $this;
    }

    /**
     * Clear $traits
     *
     * @return $this
     */
    public function clearTraits()
    {
        $this->traits = [];
        return $this;
    }

    /**
     * Unset a trait
     *
     * @param $name
     * @return $this
     */
    public function removeTrait($name)
    {
        $index = array_search($name, $this->traits);

        if ($index !== false) {
            unset($this->traits[$index]);
        }
        return $this;
    }

    /**
     * Return TRUE if $name trait is set
     *
     * @param $name
     * @return bool
     */
    public function hasTrait($name)
    {
        return array_search($name, $this->traits) !== false;
    }



    /**
     * Getter for $interfaces
     *
     * @return array
     */
    public function getInterfaces()
    {
        return $this->interfaces;
    }

    /**
     * Setter for $costants
     *
     * @param array $interfaces
     */
    public function setInterfaces(array $interfaces)
    {
        $this->interfaces = $interfaces;
    }

    /**
     * add interface to $interface
     *
     * @param $name
     * @param $value
     * @return $this
     */
    public function addInterface($value)
    {
        $this->interfaces[] = $value;
        return $this;
    }

    /**
     * Clear $interfaces
     *
     * @return $this
     */
    public function clearInterfaces()
    {
        $this->interfaces = [];
        return $this;
    }

    /**
     * Unset a interface
     *
     * @param $name
     * @return $this
     */
    public function removeInterface($name)
    {
        $index = array_search($name, $this->interfaces);

        if ($index !== false) {
            unset($this->interfaces[$index]);
        }
        return $this;
    }

    /**
     * Return TRUE if $name interface is set
     *
     * @param $name
     * @return bool
     */
    public function hasInterface($name)
    {
        return array_search($name, $this->interfaces) !== false;
    }


    /**
     * Getter for $methods
     *
     * @return Method[]
     */
    public function getMethods()
    {
        return $this->methods;
    }

    /**
     * Get method from name
     *
     * @param $method
     * @return Method
     */
    public function getMethod($method)
    {
        return $this->methods[$method];
    }

    /**
     * Return TRUE si la method exist
     *
     * @param $method
     * @return bool
     */
    public function hasMethod($method)
    {
        return !empty($this->methods[$method]);
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
            $a = preg_replace('#^'.str_replace('\\', '\\\\', $c).'#', $alias, $class);
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


        foreach($docblock->getTags() as $tag) {

            switch ($tag->getName()) {
//                case 'property' :
//                    dd('dslkjnd');
//                    $this->addTagProperty();
                case 'validate' :
                    $this->addTag($tag->getName(), $tag->getDescription());
                    break;
                default:
            }
        }


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

        // @todo Gestion des interfaces
//        $interface = $reflection->getImmediateInterfaces();
        // traits

        foreach ($reflection->getTraitNames() as $traitName) {
            $this->addTrait($traitName);
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
     * Suppression d'une methode
     *
     * @param $name
     * @return $this
     */
    public function removeMethod($name)
    {
        // Si la methode existe, on l'enleve du referenceiel
        if ($this->hasMethod($name)) {
            unset($this->methods[$name]);
        }

        return $this;
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

        if(!class_exists($class, true)){
            \exc('La classe "' . $class . '" n\'existe pas!');
        }

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
     * CReate a classe from a shortname
     *
     * @param $shortname
     * @return Maker
     */
    static public function initFromShortName($shortname)
    {
        return static::init(static::formatClassName($shortname));
    }


    /**
     * Creation d'un fichier pour inititaliser une class
     *
     * @param $class
     * @param $file
     */
    static public function init($class, $file = null)
    {
        // determination du fichier en focntion du nom de la classe
        if (is_null($file)) {
            $file = static::findFile($class);
        }

        // recuperation du realpath du fichier
        $file = app_path('../' . $file);



        if (file_exists($file)) {
            exc('Impossible de créer la classe "'.$class.'", Le fichier existe deja : ' . $file );
        }

        // initialisation du gestionnaire de fichier
        $filesystem = new Filesystem();

        // on regarde sir le fichier exist deja
        if ($filesystem->exists($file)) {
            exc('Le fichier exists déjà : ' . $file);
        }

        // creation du repertoire
        $dir = dirname($file);
        if (!is_dir($dir)) {
            $filesystem->makeDirectory($dir);
        }

        //gestion du namespace
        $body = '<?php ';
        if (preg_match("#\\\\(?<namespace>.+)\\\\(?<class>[^\\\\]+)$#", $class, $match)) {
            $body .= 'namespace ' . $match['namespace'] . ';' . PHP_EOL;
            $body .= 'class ' . $match['class'] . '{}';
        } else {
            $body .=
            $body .= 'class ' . $class . '{}';
        }

        // Création du fichier
        $filesystem->put($file, $body);

        // Chargement
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

    /**
     * Renvcoie le nom de la class depuis un fichier
     *
     * @param $file
     * @return string
     */
    static function findClass($file)
    {
        // recuperation contenu du fichier
        $content = file_get_contents($file);

        // initialisation
        $class = '\\';

        // identification du namespace
        if (preg_match('#namespace\s+(?<namespace>[^\s^;]+)#', $content, $match)) {
            $class .= trim($match['namespace']) . '\\';
        }

        // identification de la classe
        if (preg_match('#\nclass\s+(?<class>[^\s\{]+)(\s|\{)?#', $content, $match)) {
            $class .= $match['class'];
        } else {
            $class = null;
        }

        return $class;
    }


    /**
     * Recuperation des classes dans un repertoire
     *
     * @param $root
     * @return array
     */
    static function findClasses($root)
    {
        // test si repertoire
        if (!is_dir($root)) {
            exc(sprintf('Le repertoire "%s" n\'existe pas', $root));
        }

        $classes = [];

        foreach (scandir($root) as $name) {

            // cas des repertoires de navigation
            if (in_array($name, ['.', '..'])) {
                continue;
            }

            // construction du chemin complet
            $path =  $root . DIRECTORY_SEPARATOR . $name;

            // cas des repertoires
            if (is_dir($path)) {
                foreach (static::findClasses($path) as $class) {
                    $classes[] = $class;
                }
            } else {
                ($class = static::findClass($path)) && ($classes[] = $class);
            }
        }

        return $classes;
    }

    /**
     * Recuperation des controllers de l'application
     *
     * @return array
     */
    static function findControllers()
    {
        $controllers = [];

        // recuperation des classes
        $classes = static::findClasses(app_path('Http/Controllers'));

        foreach ($classes as $class) {
            $reflection = ReflectionClass::createFromName($class);

            if ($class{0} == '\\') {
                $class = substr($class, 1);
            }

            // recuperation des controllers
            if ($reflection->getParentClass()->getName() != static::NAMESPACE_CONTROLLER . 'Controller') {
                continue;
            }

            $controllers[] = str_replace(static::NAMESPACE_CONTROLLER, '', $class);
        }

        return $controllers;
    }


    /**
     * Recuperation des controllers de l'application
     *
     * @return array
     */
    static function findDb()
    {
        $dbs = [];

        // Frenchfrogs classes
        $classes =  static::findClasses(frenchfrogs_path('App/Models/Db'));

        // recuperation des classes
        foreach(static::findClasses(app_path('Models/Db')) as $class) {
            $classes[] = $class;
        }

        // recvherche de la class
        foreach ($classes as $class) {
            $reflection = ReflectionClass::createFromName($class);

            if (!$reflection->hasProperty('table')) {
                continue;
            }

            if ($class{0} == '\\') {
                $class = substr($class, 1);
            }

            $dbs[$class] = $reflection->getProperty('table')->getDefaultValue();
        }

        return $dbs;
    }


    /**
     * Find model for a specifidc tablre
     *
     * @param $table
     * @return mixed
     */
    static function findTable($table)
    {
        return collect(static::findDb())->search($table);
    }



    /**
     * Find permission constant
     *
     * @return array
     */
    public function getPermissionsConstants()
    {
        $permissions = [];

        // ANALYSE DES CONSTANTES
        foreach ($this->getConstants() as $name => $value) {
            $match = [];
            if (preg_match('#^PERMISSION_(?<permission>.+)#', $name, $match)) {
                if (preg_match('#GROUP_#', $match['permission'])) {
                    continue;
                }

                $permissions[] = $name;
            }
        }

        return $permissions;
    }

    /**
     * Find PErmission groups constant
     *
     */
    public function getPermissionsGroupsConstants()
    {

        // ANALYSE DES CONSTANTES
        $groups = [];
        foreach ($this->getConstants() as $name => $value) {
            if (preg_match('#^PERMISSION_GROUP_.+#', $name)) {
                $groups[$name] = $value;
            }
        }

        return $groups;
    }


    /**
     * Get interface constants
     *
     * @return array
     */
    public function getInterfacesConstants()
    {
        $interfaces = [];
        $rulerClass = $this->getClass()->getName();
        $interfaces['INTERFACE_DEFAULT'] = $rulerClass::INTERFACE_DEFAULT;
        foreach ($this->getConstants() as $name => $value) {
            if (preg_match('#^INTERFACE_.+#', $name) && $name != 'INTERFACE_DEFAULT') {
                $interfaces[$name] = $value;
            }
        }

        return $interfaces;
    }


    /**
     * Donne le nom du fichier pour une classe
     *
     * @param $class
     * @return string
     */
    static function findFile($class)
    {
        // on s'assure que le namespace par bien du debut
        if ($class{0} != '\\') {
            $class = '\\' . $class;
        }

        /// gestion des namespace
        $class = preg_replace('#^.App(.+)#', 'app$1', $class);
        $class = str_replace('\\', DIRECTORY_SEPARATOR, $class);

        return $class . '.php';
    }

    /**
     * Build class name from short naming
     *
     * @param $shortname
     * @return string
     */
    static public function formatClassName($shortname)
    {
        $shortname = str_replace('.', '\\_', $shortname);
        $class = ucfirst(camel_case($shortname));
        return '\\' . $class;
    }
}