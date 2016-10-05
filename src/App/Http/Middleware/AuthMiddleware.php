<?php namespace FrenchFrogs\App\Http\Middleware;

use Carbon\Carbon;
use Closure;
use FrenchFrogs\App\Models\Acl;
use Illuminate\Auth\SessionGuard;
use Illuminate\Auth\TokenGuard;
use Illuminate\Contracts\Auth\Factory as Auth;

class AuthMiddleware
{
    protected $interface = Acl::INTERFACE_DEFAULT;
    protected $viewLogin = 'login';
    protected $redirectionSessionKey;

    /**
     * Create a new filter instance.
     *
     * @param  Auth $auth
     * @return void
     */
    public function __construct(Auth $auth)
    {
        $this->auth = $auth;
    }


    /**
     *
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\Http\RedirectResponse|\Illuminate\View\View
     */
    public function login()
    {
        $request = \request();
        $error = false;
        $email = '';

        $auth = \auth();

        if ($auth->check()) {
            return redirect()->route('home');
        } elseif ($request->has('email')) {

            // recuperationd es information
            $error = true;
            $email = $request->get('email');
            $password = $request->get('password');
            $remember = $request->get('remember');

            //Authentification
            if ($auth->attempt(['email' => $email, 'password' => $password, 'user_interface_id' => $this->interface], $remember)) {
                $auth->user()->update(['loggedin_at' => Carbon::now()]);

                // redirection
                $url = $this->getRedirect();
                $this->forgetRedirect();
                return \redirect()->to($url);
            }
        }

        return \response()->view($this->viewLogin, ['error' => $error, 'email' => $email]);
    }

    /**
     * Setter for redicrection URL
     *
     * @param $url
     */
    public function setRedirect($url)
    {
        \Session::put($this->redirectionSessionKey, $url);
    }

    /**
     * Return TRUE if a redirection url is set
     *
     * @return bool
     */
    public function hasRedirect()
    {
        return \Session::has($this->redirectionSessionKey);
    }

    /**
     * Gettyter foir redirection URL
     *
     * @return mixed
     */
    public function getRedirect()
    {
        return $this->hasRedirect() ? \Session::get($this->redirectionSessionKey) : '/';
    }

    /**
     *
     * Forget redirection URL
     *
     */
    public function forgetRedirect()
    {
        \Session::forget($this->redirectionSessionKey);
    }


    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  \Closure $next
     * @return mixed
     */
    public function handle($request, Closure $next, $interface = null, $viewLogin = null)
    {

        // surcharge de l'interface
        if (!is_null($interface)) {
            $this->interface = $interface;
            \Auth::shouldUse($interface);
        }

        // determination de l'authentification
        $is_guest = \auth()->guest();

        // si pas authentifié on effectue la redirection
        if ($is_guest) {
            if ($request->ajax() || $request->wantsJson()) {
                return response('Unauthorized.', 401);
            } else {

                // Cas d'un authentification en sessions (page web)
                if (auth()->guard() instanceof SessionGuard) {

                    // surcharge de l'interface
                    if (!is_null($viewLogin)) {
                        $this->viewLogin = $viewLogin;
                    }

                    // set redirection session key
                    $this->redirectionSessionKey = 'login.' . \Auth::getName() . '.redirect';

                    // gestion d'une url de redirection
                    $url = $request->getRequestUri();
                    if ($url != '/') {
                        $this->hasRedirect() ?: $this->setRedirect($url);
                        return \redirect()->to('/');
                    }

                    // envoie du login
                    return $this->login();
                }


//                // Cas d'un api
//        if (auth()->guard() instanceof TokenGuard) {
//
//            dd('dslj;hdlkd');
//        }

                // comportement normale
                return redirect()->guest('login');
            }
        }

        return $next($request);
    }
}
