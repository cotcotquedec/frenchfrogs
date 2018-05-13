<?php namespace FrenchFrogs\App\Http\Middleware;


use Closure;
use Illuminate\Http\Response;

class ModalMiddleware
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

        // ne traite que les demandes ajax
        if ($request->ajax()) {

            try {
                // Get origin content
                $original = $response->getOriginalContent();

                // Initialisation de la modal
                $modal = '';

                // MODAL
                if ($original instanceof \FrenchFrogs\Modal\Modal\Modal) {
                    $modal = $original->enableRemote();

                    //FORM
                } elseif ($original instanceof \FrenchFrogs\Form\Form\Form) {

                    // on change le renderer
                    $renderer = ff()->get('form.renderer_modal');
                    $modal = $original->setRenderer(new $renderer());
                }

                throw_if(empty($modal), 'Impossible de determiner la modal');

                //  on Ajouter le JS onload
                $modal .= '<script>jQuery(function() {' . js('onload') . '});</script>';

                // Rétribution du nouveau contenu
                $response->setContent($modal);
            } catch (\Throwable $e) {
                debugbar()->addThrowable($e);
                // On renvoie une erreur pour informer l'utilisateur
                $response = response()->json(['error' => $e->getMessage()], 500);
            }
        }

        return $response;
    }
}
