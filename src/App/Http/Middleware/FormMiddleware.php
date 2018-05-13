<?php namespace FrenchFrogs\App\Http\Middleware;


use Closure;
use FrenchFrogs\Form\Form\Form;
use Illuminate\Http\Response;
use Illuminate\Validation\ValidationException;

class FormMiddleware
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

        // Cas d'un traitement de formulaire
        if ($request->isMethod('POST') && $original instanceof Form) {
            try {
                $original->setDataFromRequest()->save() &&
                js()->success()->closeRemoteModal()->reloadDataTable();
            } catch (ValidationException $e) {
                js()->warning();
            } catch (\Throwable $e) {
                debugbar()->addThrowable($e);
                js()->error();
            }
        }

        return $response;
    }
}
