<?php namespace FrenchFrogs\App\Http\Middleware;

use Closure;
use FrenchFrogs\Core\Configurator;

class FrenchFrogsMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next, $configuration)
    {
        Configurator::setNamespaceDefault($configuration);
        return $next($request);
    }
}
