<?php namespace FrenchFrogs\Core;


use Illuminate\Http\Request;

trait FrenchFrogsController
{


    /**
     * @var Request
     */
    protected $request;


    /**
     * Recuperation de la request courante
     *
     * @return Request
     */
    public function request()
    {
        // si la requete n'existe pas on l'a crée
        if (!$this->request) {
            $request = \request();
            $request->merge(\Route::current()->parameters());

            // formatage
            $format = [];
            foreach ($request->all() as $k => $v) {
                if (preg_match('#[0-9A-Z]{32}#', $v)) {
                    $format['__' . $k] = uuid($v)->bytes;
                }
            }
            $request->merge($format);

            $this->request = $request;
        }

        return $this->request;
    }

    /**
     * getter for l'utilisateur courant
     *
     * @return \Illuminate\Contracts\Auth\Authenticatable|null
     */
    public function user()
    {
        return \auth()->user();
    }


    /**
     * Filter values and inject them into current request
     *
     * @param array $rules
     * @return $this
     */
    public function filter(array $rules)
    {

        // recupération de la requete
        $request = $this->request();

        // intitilisation des data a merger
        $to_merge = [];

        // pour chaque regle on applique un filtre
        foreach($rules as $key => $rule) {
            if(!$request->has($key)) { continue;}
            $to_merge[$key] = f($request->get($key), $rule);
        }

        // on ajoute les data a la requete courante
        $request->merge($to_merge);

        return $this;
    }
}