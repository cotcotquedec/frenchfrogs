<?php namespace FrenchFrogs\Core;
use App\Http\Controllers\Controller;

/**
 *
 * Serialization et adressage d'une execution
 *
 * Class Nenuphar
 * @package FrenchFrogs\Core
 */
class Nenuphar
{

    /**
     * @var string
     */
    protected $interpreter;


    /**
     * @var
     */
    protected $class;


    /**
     * @var
     */
    protected $method;


    /**
     * @var
     */
    protected $params;

    /**
     * @var variable supplementaires
     */
    protected $extras = [];

    /**
     * @var
     */
    protected $token;

    /**
     * Nenuphar constructor.
     *
     *
     * @param string $class
     * @param string|null $method
     * @param array $params
     * @param string $interpreter
     * @param array $extras
     */
    public function __construct(string $class, string $method = null, array $params = [], string $interpreter = 'default', $extras = [])
    {
        $this->class = $class;
        $this->method = $method;
        $this->params = $params;
        $this->setExtras($extras);
        $this->interpreter = $interpreter;
        $this->token = uuid()->string;
    }

    /**
     * @param array $extra
     * @return $this
     */
    public function setExtras(array $extras)
    {
        $this->extras = $extras;
        return $this;
    }

    /**
     *
     *
     * @return variable
     */
    public function getExtras()
    {
        return $this->extras;
    }

    /**
     *
     */
    protected function buildDefault()
    {

        dd('Fonction non implementé');
//        $instance = new \ReflectionClass($this->class);
//        $class = $instance->newInstance();
//        $table = call_user_func_array([$controller, $method], $params);
    }


    /**
     * Interpreter pour un controller
     *
     *
     * @return Controller
     */
    public function buildController()
    {
        $controller = app()->make($this->class);
        return $controller->callAction($this->method, $this->params);
    }


    /**
     *
     */
    public function execute()
    {
        $interpreter = camel_case('build_' . $this->interpreter);

        if (!method_exists($this, $interpreter)) {
            throw new \Exception('L\'interpreteur n\'existe pas : ' . $interpreter);
        }

        return $this->$interpreter();
    }

    /**
     * Enregistre la configuration en session
     *
     *
     * @return $this
     */
    public function register($token = null)
    {

        if (!empty($token)) {
            $this->token = $token;
        }

        // Enregiustrement en session
        session()->put($this->token, $this->serialize());

        return $this;
    }


    /**
     * Setter for token
     *
     * @param $token
     * @return $this
     */
    public function setToken($token)
    {
        $this->token = $token;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getToken()
    {
        return $this->token;
    }

    /**
     * Serialization
     *
     * @return string
     * @throws \Exception
     */
    public function serialize()
    {

        // test si serializable
        try {
            $params = json_encode($this->params);

            if (json_decode($params, JSON_OBJECT_AS_ARRAY) != $this->params) {
                throw new \Exception('Perte sur la serialization des paramètres');
            }

            $extras = json_encode($this->extras);
            if (json_decode($extras, JSON_OBJECT_AS_ARRAY) != $this->extras) {
                throw new \Exception('Perte sur la serialization des extras');
            }
        } catch (Exception $e) {
            throw new \Exception('Erreur sur la serializationdes paramètre : ' . $e->getMessage());
        }

        return $this->class . '@' . $this->method . '|' . $this->interpreter . ':' . base64_encode($params) . '#' . base64_encode($extras);
    }

    /**
     *
     *
     *
     * @return static
     */
    static public function fromToken(string $token)
    {
        if (!session()->has($token)) {
            throw new \Exception('Impossible de trouver le token dans le session : ' . $token) ;
        }

        // Reconstruction des paramètre de construction
        $match = [];
        preg_match('#^(?<class>[^@]+)@(?<method>[^\|]+)\|(?<interpreter>[^:]+):(?<params>[^\#]+)\#(?<extras>.+)$#', session($token), $match);

        /// Formatage des paramètre
        $params = base64_decode($match['params']);
        $params = json_decode($params, JSON_OBJECT_AS_ARRAY);

        // Formatage des extras
        $extras = base64_decode($match['extras']);
        $extras = json_decode($extras, JSON_OBJECT_AS_ARRAY);

        return (new static($match['class'], $match['method'], $params, $match['interpreter'], $extras))->setToken($token);
    }

}