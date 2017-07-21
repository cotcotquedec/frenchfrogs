<?php namespace FrenchFrogs\App\Models;

use BetterReflection\Reflection\ReflectionClass;

/**
 * Class Route
 * @package FrenchFrogs\App\Models
 */
class Route
{

    /**
     * Controller de l'application reflect
     *
     * @var array
     */
    protected $controllers = [];

    /**
     * @var array
     */
    protected $routes = [];


    /**
     * Constructeur
     *
     * Route constructor.
     * @param array $controllers
     */
    public function __construct(array $controllers = [])
    {
        $this->controllers = $controllers;
    }


    /**
     * GEtter for $routes
     *
     * @return array
     */
    public function getRoutes()
    {
        return $this->routes;
    }


    /**
     * Setter for $routes
     *
     * @param array $routes
     * @return $this
     */
    public function setRoutes(array $routes)
    {
        $this->routes = $routes;
        return $this;
    }

    /**
     * Chargement des route a partir des controller
     *
     * @return static
     *
     */
    static function load(array $controllers = [], $force_reload = false)
    {
        $class = new static($controllers);

        // generation de la clé de cache
        $key = 'route.controllers.' . md5(json_encode($controllers));

        // si oùn force la suppression du cache
        if ($force_reload) {
            cache()->forget($key);
        }

        // recuperation des routes dans le cache
        $routes = cache($key);

        if (empty($routes)) {
            foreach ($class->getControllers() as $prefix => $controller) {
                $class->loadRoutesFromController($prefix, $controller);
            }

            cache()->forever($key, $class->getRoutes());
        } else {
            $class->setRoutes($routes);
        }

        return $class;
    }

    /**
     * Register routes
     *
     */
    public function register()
    {
        foreach ($this->routes as $key => $route) {
            //
            $route = call_user_func_array(
                [\Route::getFacadeRoot(), $route['action']],
                [$route['uri'], sprintf('%s@%s', '\\' . $route['controller'], $route['name'])]
            );

            // nomage de la route
            $route->name($key);
        }
    }

    /**
     * getter for $controllers
     *
     * @return array
     */
    public function getControllers()
    {
        return $this->controllers;
    }

    /**
     * Load route for $controller
     *
     * @param $prefix
     * @param $controller
     */
    public function loadRoutesFromController($prefix, $controller)
    {
        // on charge le controller
        new \ReflectionClass($controller);
        $reflection = ReflectionClass::createFromName($controller);

        // on scan les methods
        foreach ($reflection->getMethods() as $method) {

            // nom de la method
            $name = $method->getName();

            // si la method appartient a Illuminate on zap
            if (preg_match('#^Illuminate#', $method->getDeclaringClass()->getName())) {
                continue;
            }

            // Recuperation de la syntaxe de la router controller
            if (preg_match('#(?<action>put|get|post|delete|any)(?<title>.+)#', $name, $match)) {

                //recuperation des informations
                $action = $match['action'];

                $title = str_slug($match['title']);

                // construction de l'url
                $uri = '/' . $prefix . ($title == 'index' ? '' : '/' . $title);

                // Gestion des paramètres
                foreach ($method->getParameters() as $parameter) {

                    // si le parètre a un type,
                    // c'est qu'il ne peux pas etre passé via l'url,
                    // on le retire donc de la gestion
                    if ($parameter->getType()) {
                        continue;
                    }

                    $uri .= sprintf('/{%s%s}', $parameter->getName(), $parameter->isDefaultValueAvailable() ? '?' : '');
                }

                // création de la rout  e

                $this->routes[sprintf('%s.%s.%s', $prefix, $title, $action)] = compact('action', 'uri', 'controller', 'name', 'prefix', 'title');
            }
        }

        return $this;
    }
}