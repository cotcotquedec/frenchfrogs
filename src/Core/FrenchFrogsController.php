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
                if (is_string($v) && preg_match('#[0-9a-zA-Z]{32}#', $v)) {
                    $format['__' . $k] = uuid($v)->bytes;
                }
            }
            $request->merge($format);

            $this->request = $request;
        }

        return $this->request;
    }


    /**
     * Surcharge de la validation pour recuperer le request
     *
     * @param Request $request
     * @param array $rules
     * @param array $messages
     * @param array $customAttributes
     * @return Request
     */
    public function validate(Request $request, array $rules, array $messages = [], array $customAttributes = [])
    {
        $validator = $this->getValidationFactory()->make($request->all(), $rules, $messages, $customAttributes);

        if ($validator->fails()) {
            $this->throwValidationException($request, $validator);
        }

        return $request;
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
}