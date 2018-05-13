<?php namespace FrenchFrogs\App\Http\Middleware;


use Closure;
use Exception;
use FrenchFrogs\Table\Table\Table;
use Illuminate\Http\Response;

class TableMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  \Closure $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        /** @var Response $response */
        $response = $next($request);

        // Recuperation du retour du controller
        $original = $response->getOriginalContent();

        try {
            if (is_array($original)) {
                return response()->json(json_decode($original));
            }

            throw_unless($original instanceof Table, new Exception('Mauvaise instance passé en paramètre : ' . get_class($original)));

            /**  @var $original Table */
            if ($request->isMethod('GET')) {
                //  on Ajouter le JS onload
                $original .= '<script>jQuery(function() {' . js('onload') . '});</script>';
                $response->setContent($original);
            }

        } catch (Exception $e) {
            debugbar()->addThrowable($e);
        }

        return $response;
    }
}
