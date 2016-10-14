<?php

namespace FrenchFrogs\App\Models;

use BetterReflection\Reflection\ReflectionClass;

/**
 * Class Route.
 */
class Route
{
    /**
     * Controller de l'application reflect.
     *
     * @var array
     */
    protected $controllers = [];

    /**
     * Constructeur.
     *
     * Route constructor.
     *
     * @param array $controllers
     */
    public function __construct(array $controllers = [])
    {
        $this->controllers = $controllers;
    }

    /**
     * Chargement des route a partir des controller.
     */
    public static function load(array $controllers = [])
    {
        $class = new static($controllers);

        foreach ($class->getControllers() as $prefix => $controller) {
            $class->loadRouteFromController($prefix, $controller);
        }
    }

    /**
     * getter for $controllers.
     *
     * @return array
     */
    public function getControllers()
    {
        return $this->controllers;
    }

    /**
     * Load route for $controller.
     *
     * @param $prefix
     * @param $controller
     */
    public function loadRouteFromController($prefix, $controller)
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
            if (preg_match('#(?<action>get|post|delete|any)(?<title>.+)#', $name, $match)) {


                //recuperation des informations
                $action = $match['action'];
                $title = str_slug($match['title']);

                // construction de l'url
                $uri = '/'.$prefix.($title == 'index' ? '' : '/'.$title);

                // Gestion des paramètres
                foreach ($method->getParameters() as $parameter) {
                    $uri .= sprintf('/{%s%s}', $parameter->getName(), $parameter->isDefaultValueAvailable() ? '?' : '');
                }

                // création de la rout  e
                $route = call_user_func_array([\Route::getFacadeRoot(), $action], [$uri, sprintf('%s@%s', '\\'.$controller, $name)]);

                // nomage de la route
                $route->name(sprintf('%s.%s.%s', $prefix, $title, $action));
            }
        }

        return $this;
    }
}
