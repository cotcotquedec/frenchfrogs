<?php namespace FrenchFrogs\Form\Form;

use FrenchFrogs\Core;
use FrenchFrogs;
use FrenchFrogs\Form\Renderer;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

/**
 * Form polliwog
 *
 * Class Form
 * @package FrenchFrogs\Form\Form
 */
class Form
{
    use \FrenchFrogs\Html\Html;
    use Core\Renderer;
    use Core\Integration\Validator;
    use Core\Panel;
    use Remote;
    use Element;
    use Core\Integration\Nenuphar;

    /**
     * Legend of the form (title)
     *
     * @var
     */
    protected $legend;


    /**
     * Specify if the form will render csrf token
     *
     * @var
     */
    protected $has_csrfToken;


    /**
     * Specify if label must be displayed
     *
     * @var
     */
    protected $has_label = true;


    /**
     * donnée a traioter par le formulaire
     *
     * @var array
     */
    protected $data = [];

    /**
     * Set $has_csrfToken to TRUE
     *
     * @return $this
     */
    public function enableCsrfToken()
    {
        $this->has_csrfToken = true;
        return $this;
    }

    /**
     * Set $has_csrfToken to FALSE
     *
     * @return $this
     */
    public function disableCsrfToken()
    {
        $this->has_csrfToken = false;
        return $this;
    }

    /**
     * enable hasLabel
     *
     * @return $this
     */
    public function enableLabel()
    {
        $this->has_label = true;
        return $this;
    }

    /**
     * disable hasLabel
     *
     * @return $this
     */
    public function disableLabel()
    {
        $this->has_label = false;
        return $this;
    }

    /**
     * getter hasLabel
     *
     * @return bool
     */
    public function hasLabel()
    {
        return $this->has_label;
    }

    /**
     * Getter for $has_csrfToken
     *
     * @return mixed
     */
    public function hasCsrfToken()
    {
        return $this->has_csrfToken;
    }


    /**
     * Setter for $legend attribute
     *
     * @param $legend
     * @return Form
     */
    public function setLegend($legend)
    {
        $this->legend = strval($legend);
        return $this;
    }

    /**
     * Getter for $legend attribute
     *
     * @return mixed
     */
    public function getLegend()
    {
        return $this->legend;
    }

    /**
     * Return TRUE if $$legend attribute is set
     *
     * @return bool
     */
    public function hasLegend()
    {
        return isset($this->legend);
    }



    /**
     * Constructor
     *
     * @param string $url
     * @param string $method
     */
    public function __construct($url = null, $method = null)
    {
        /*
        * Configure polliwog
        */
        $c = ff();
        $class = $c->get('form.renderer');
        $this->setRenderer(new $class);

        $this->has_csrfToken = $c->get('form.csrf', true);

        //default configuration
        $this->addAttribute('id', 'form-' . rand());

        // if method "init" exist, we call it.
        if (method_exists($this, 'init')) {
            $this->setMethod($c->get('form.method', 'POST'));
            call_user_func_array([$this, 'init'], func_get_args());
        } else {
            $this->setUrl($url);
            $this->setMethod(is_null($method) ? $c->get('form.method', 'POST') : $method);
        }

        // default url
        if (empty($this->getUrl())) {
            $this->setUrl(str_replace(request()->getSchemeAndHttpHost(), '', request()->fullUrl()));
        }
    }

    /**
     * Set method
     *
     * @param $method
     * @return $this
     */
    public function setMethod($method)
    {
        return $this->addAttribute('method', $method);
    }


    /**
     * Get method
     *
     * @return string
     */
    public function getMethod()
    {
        return $this->getAttribute('method');
    }

    /**
     * Set action URL
     *
     * @param $action
     * @return $this
     */
    public function setUrl($action)
    {
        $this->addAttribute('action', $action);
        return $this->addAttribute('url', $action);
    }

    /**
     * get action URL
     *
     *
     * @return string
     */
    public function getUrl()
    {
        return $this->getAttribute('url');
    }


    /**
     * Magic method for exceptional use
     *
     * @param $name
     * @param $arguments
     */
    function __call($name, $arguments)
    {

        if (preg_match('#add(?<type>\w+)#', $name, $match)) {

            // cas des action
            if (substr($match['type'], 0, 6) == 'Action') {
                $type = substr($match['type'], 6);
                $class = new \ReflectionClass(__NAMESPACE__ . '\Element\\' . $type);
                $e = $class->newInstanceArgs($arguments);
                $this->addAction($e);

                // cas des element
            } else {
                $namespace = __NAMESPACE__;
                if (!empty(ff()->get('form.namespace'))) {
                    $namespace = ff()->get('form.namespace');
                }
                $class = new \ReflectionClass($namespace . '\Element\\' . $match['type']);
                $e = $class->newInstanceArgs($arguments);
                $this->addElement($e);
            }
        }
    }

    /**
     * Render the polliwog
     *
     * @return mixed|string
     */
    public function render()
    {
        $render = '';
        try {
            $render = $this->getRenderer()->render('form', $this);
        } catch (\Exception $e) {
            debugbar()->addThrowable($e);
        }

        return $render;
    }


    /**
     * Overload parent method for form specification
     *
     * @return string
     *
     */
    public function __toString()
    {
        return $this->render();
    }


    /**
     * @param array ...$arg
     * @return \Illuminate\Validation\Validator
     * @throws \Throwable
     */
    public function make(...$arg)
    {
        $v = $this->getValidator()->make(...$arg);
        $this->populate($v->getData());
        return $v;
    }

    /**
     *
     * Fill the form with $values
     *
     * @param array|Arrayable $values
     * @return $this
     */
    public function populate($values, $alias = false)
    {

        if (is_object($values) && method_exists($values, 'toArray')) {
            $values = $values->toArray();
        }


        foreach ($this->getElements() as $e) {
            /** @var $e \FrenchFrogs\Form\Element\Element */
            $name = $alias && $e->hasAlias() ? $e->getAlias() : $e->getName();
            if (array_key_exists($name, $values) !== false) {
                $e->setValue($values[$name]);
            }
        }

        return $this;
    }


    /**
     * Return the value single value of the $name element
     *
     * @param $name
     * @return mixed
     */
    public function getValue($name)
    {
        return $this->getElement($name)->getValue();
    }


    /**
     * Return all values from all elements
     *
     * @return array
     */
    public function getValues()
    {

        $values = [];
        foreach ($this->getElements() as $name => $e) {
            /** @var $e \FrenchFrogs\Form\Element\Element */
            if ($e->isDiscreet()) {
                continue;
            }
            $values[$name] = $e->getValue();
        }

        return $values;
    }

    public function detectAction()
    {

        // On cherche l'action qui a été lancé
        foreach ($this->data as $k => $v) {
            if (preg_match('#action__[0-9a-fA-F]{32}#', $k)) {
                return $k;
            }
        }

        return false;
    }


    /**
     * Set les datadu form depuis une request
     *
     * @param Request|null $request
     * @return $this
     * @throws \Throwable
     */
    public function setDataFromRequest(Request $request = null)
    {

        // si pas de request spoécifié, on passe la requete courante
        $request = $request ?: \request();
        $this->setData($request);

        return $this;
    }


    /**
     *
     * @param null $data
     * @return $this
     * @throws \Throwable
     */
    public function setData($data)
    {

        // Si pas un tableau on traite
        if (!is_array($data)) {

            // si on ne pas transformer l'object entableau, on envoie une exception
            throw_unless(method_exists($data, 'toArray'), 'Impossible de recuperer les valeur pour le formulaire : ' . $this->getLegend());
            $data = $data->toArray();
        }

        // Verification des donnée
        throw_unless(is_array($data), 'Les données transmise au formulaire ne sont pas un tableau');

        // Setter
        $this->data = $data;

        return $this;
    }

    /**
     *
     * @return array
     * @throws \Throwable
     */
    public function getData()
    {
        // Si pas de data on essaie de les detecté automatiquement
        is_null($this->data) && $this->setData();

        return $this->data;
    }


    /**
     * Saéuvegarde d'un formulaire
     *
     * @param null $function
     * @return bool
     * @throws ValidationException
     * @throws \Throwable
     */
    public function save($function = null)
    {
        // DATA
        $data = $this->getData();

        $return = false;


        // Validation
        $this->validate($data);

        // Recuperationd es donnée du formulaire
        $values = $this->getValues();

        // Detection automatiuque d'une action
        if (is_null($function) && $this->hasActions()) {

            // Identific ation de l'action
            $action = $this->detectAction();

            //
            if ($this->hasAction($action)) {
                $return = $this->getAction($action)->process($values);
            }
        }

        // Cas d'un callable
        if (is_callable($function) && !is_string($function)) {
            $return = $function($values, $this);
        }

        // Cas d'un model
        if ($function instanceof Model) {
            $return = $function->fill($values)->save();
        }

        throw_if(empty($return), 'Impossible de determiner le processus de sauvegarde du formulaire');

        return $return;
    }
}
