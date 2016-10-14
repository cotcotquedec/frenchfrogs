<?php

namespace FrenchFrogs\Table\Table;

use InvalidArgumentException;
use Session;

trait Datatable
{
    /**
     * @var
     */
    protected $is_datatable;

    /**
     * @var
     */
    protected $is_remote;


    /**
     * Session token for remote.
     *
     * @var
     */
    protected $token;


    /**
     * If a single token if set, it mean that this class can only have a single instance.
     *
     * @var
     */
    protected static $singleToken;


    /**
     * String for remote constructor.
     *
     * @var string
     */
    protected $constructor;


    /**
     * Search function.
     *
     * @var callable
     */
    protected $search;


    /**
     * Button for Datatable.
     *
     * @see https://datatables.net/reference/button/
     *
     * @var array
     */
    protected $datatableButtons = [];

    /**
     * Ajoute un bouton.
     *
     * @param $text
     * @param $action
     *
     * @return $this
     */
    public function addDatatableButton($text, $action)
    {
        $this->datatableButtons[] = ['text' => $text, 'action' => $action];

        return $this;
    }

    /**
     * Ajoute un lien au button datatable.
     *
     * @param $text
     * @param $url
     *
     * @return $this
     */
    public function addDatatableButtonLink($text, $url)
    {
        $this->addDatatableButton($text, 'function() {window.open("'.$url.'")}');

        return $this;
    }

    /**
     * Ajoute un Bouton d'export.
     *
     * @param $text
     * @param $url
     *
     * @return $this
     */
    public function addDatatableButtonExport($text = 'Export CSV')
    {
        $this->hasToken() ?: $this->generateToken();
        $this->addDatatableButtonLink($text, route('datatable-export', ['token' => $this->getToken()]));

        return $this;
    }

    /**
     * Reset les filtres.
     *
     * @param string $text
     *
     * @return $this
     */
    public function addDatatableButtonReset($text = 'Reset')
    {
        $this->addDatatableButton($text, 'function() {jQuery("table#'.$this->getAttribute('id').'").dataTable().fnClearFilters();}');

        return $this;
    }

    /**
     * Return TRUE si au moins un bouton est ajouté.
     *
     * @return bool
     */
    public function hasDatatableButtons()
    {
        return is_array($this->datatableButtons) && count($this->datatableButtons) > 0;
    }

    /**
     * Clear $buttons.
     *
     * @return $this
     */
    public function clearDatatableButtons()
    {
        $this->datatableButtons = [];

        return $this;
    }

    /**
     * Getter for $datatabaseButtons.
     *
     * @return array
     */
    public function getDatatableButtons()
    {
        return $this->datatableButtons;
    }

    /**
     * Setter for $search callable function.
     *
     * @param $function
     *
     * @return $this
     */
    public function setSearch($function)
    {
        if (is_string($function)) {
            $field = $function;
            $function = function (Table $table, $query) use ($field) {

                // verify that source is a query
                if (!$this->isSourceQueryBuilder()) {
                    throw new \Exception('Table source is not an instance of query builder');
                }

                $table->getSource()->where($field, 'LIKE', $query.'%');
            };
        }

        if (!is_callable($function)) {
            throw new InvalidArgumentException('Search function is not callable');
        }

        $this->search = $function;

        return $this;
    }

    public function removeSearch()
    {
        unset($this->search);

        return $this;
    }

    public function hasSearch()
    {
        return isset($this->search);
    }

    /**
     * Search.
     *
     * @param null $query
     */
    public function search($query)
    {
        if ($this->hasSearch()) {
            $search = $this->search;

            // If it's a anonymous function
            if (!is_string($search) && is_callable($search)) {
                call_user_func($search, $this, $query);
            }
        }

        return $this;
    }

    /**
     * @return bool
     */
    public static function hasSingleToken()
    {
        return !empty(static::$singleToken);
    }

    /**
     * @return mixed
     */
    public static function getSingleToken()
    {
        return static::$singleToken;
    }

    /**
     * Set TRUE to $is_remote.
     *
     * @return $this
     */
    public function enableRemote()
    {
        $this->is_remote = true;

        return $this;
    }

    /**
     * Set FALSE to $is_remote attribute.
     *
     * @return $this
     */
    public function disableRemote()
    {
        $this->is_remote = false;

        return $this;
    }

    /**
     * Return TRUE is datatable ajax is enable.
     *
     * @return mixed
     */
    public function isRemote()
    {
        return $this->is_remote;
    }

    /**
     * Set $is_datatable attribute.
     *
     * @param $datatable
     *
     * @return $this
     */
    public function enableDatatable()
    {
        $this->is_datatable = true;

        return $this;
    }

    /**
     * Set to FALSE $is_datatable.
     *
     * @return $this
     */
    public function disableDatatable()
    {
        $this->is_datatable = false;

        return $this;
    }

    /**
     * Return TRUE if datatable is enabled.
     *
     * @return mixed
     */
    public function isDatatable()
    {
        return $this->is_datatable;
    }

    /**
     * Return true if $is_datatable is set.
     *
     * @return bool
     */
    public function hasDatatable()
    {
        return isset($this->is_datatable);
    }

    /**
     * Getter for $token attribute.
     *
     * @return mixed
     */
    public function getToken()
    {
        return $this->token;
    }

    /**
     * Setter for $token attribute.
     *
     * @param $token
     *
     * @return $this
     */
    public function setToken($token)
    {
        $this->token = strval($token);

        return $this;
    }

    /**
     * Return TRUE if the datatble have a token.
     *
     * @return bool
     */
    public function hasToken()
    {
        return isset($this->token);
    }

    /**
     * Generate a token and fill it.
     *
     * @return $this
     */
    public function generateToken()
    {
        $this->token = 'table.'.md5(static::class.microtime());

        return $this;
    }

    /**
     * Getter for $constructor attribute.
     *
     * @return string
     */
    public function getConstructor()
    {
        return $this->constructor;
    }

    /**
     * Setter for $constructor attribute.
     *
     * @param $constructor
     * @param null $method
     * @param null $params
     *
     * @return $this
     */
    public function setConstructor($constructor, $method = null, $params = null)
    {
        if (!is_null($method)) {
            $constructor .= '::'.$method;
        }

        if (!is_null($params)) {
            $constructor .= ':'.$params;
        }

        $this->constructor = $constructor;

        return $this;
    }

    /**
     * Unset $constructor attribute.
     *
     * @return $this
     */
    public function removeConstructor()
    {
        unset($this->constructor);

        return $this;
    }

    /**
     * Return TRUE if $constructor attribute is set.
     *
     * @return bool
     */
    public function hasConstructor()
    {
        return isset($this->constructor);
    }

    /**
     * Save the Table configuration in Session.
     */
    public function save($query = null)
    {

        // si pas de token
        if (!$this->hasToken()) {
            if (static::hasSingleToken()) {
                $this->setToken(static::getSingleToken());
            } else {
                $this->generateToken();
            }
        }

        // Si le constructeur n'est pas setter
        if (!$this->hasConstructor()) {
            $this->setConstructor(static::class);
        }

        // on enregistre la session
        Session::set($this->getToken(), json_encode(['constructor' => $this->getConstructor(), 'query' => $query]));

        return $this;
    }

    /**
     * Load a datable from a token.
     *
     * @param $token
     *
     * @return Table
     */
    public static function load($token = null, $processQuery = false)
    {
        // if single token is set, we keep it
        if (is_null($token) && static::hasSingleToken()) {
            $token = static::getSingleToken();
        }

        // if no session are set, we set a new one only is class has a singleToken
        if (!Session::has($token)) {
            if (static::hasSingleToken()) {
                Session::push($token, json_encode(['constructor' => static::class]));
            } else {
                throw new \InvalidArgumentException('Token "'.$token.'" is not valid');
            }
        }

        // load configuration
        $config = Session::get($token);
        $config = \json_decode($config, true);
        $constructor = $config['constructor'];

        // construct Table polliwog
        if (preg_match('#(?<class>.+)::(?<method>.+)#', $constructor, $match)) {
            $method = $match['method'];
            // extract parameters
            $params = [];
            if (preg_match('#(?<method>[^:]+):(?<params>.+)#', $method, $params)) {
                $method = $params['method'];
                $params = explode(',', $params['params']);
            }

            $table = call_user_func_array([$match['class'], $method], $params);
        } else {
            $instance = new \ReflectionClass($config->constructor);
            $table = $instance->newInstance();
        }

        // attribution du token
        $table->setToken($token);

        // validate that instance is viable
        if (!($table instanceof static)) {
            throw new \Exception('Loaded instance is not viable');
        }

        // process query
        if ($processQuery && !empty($config['query'])) {
            $query = $config['query'];

            $columns = $query['columns'];
            $search = empty($query['search']) ? null : $query['search'];
            $order = empty($query['order']) ? null : $query['order'];
            $table->processQuery($columns, $search, $order);
        }

        return $table;
    }

    /**
     * Process a query.
     *
     * @param $columns
     * @param null $search
     * @param null $order
     *
     * @return $this
     */
    public function processQuery($columns, $search = null, $order = null)
    {
        // gestion des reccherches
        foreach ($columns as $c) {
            if ($c['searchable'] == 'true' && $c['search']['value'] != '') {
                $this->getColumn($c['name'])->getStrainer()->call($this, $c['search']['value']);
            }
        }

        // gestion de la recherche globale
        if (!empty($search['value'])) {
            $this->search($search['value']);
        }

        // gestion du tri
        if (!empty($order)) {
            if ($this->isSourceQueryBuilder()) {
                $this->getSource()->orders = [];
            }

            foreach ($order as $o) {
                extract($o);
                $this->getColumnByIndex($column)->order($dir);
            }
        }

        return $this;
    }
}
