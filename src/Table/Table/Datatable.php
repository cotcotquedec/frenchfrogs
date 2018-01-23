<?php namespace FrenchFrogs\Table\Table;

use FrenchFrogs\Core\Nenuphar;
use InvalidArgumentException;
use Session;

trait Datatable
{

    /**
     *
     *
     * @var
     */
    protected $is_datatable;

    /**
     *
     *
     * @var
     */
    protected $is_remote;

    /**
     * Search function
     *
     * @var Callable
     */
    protected $search;


    /**
     * Button for Datatable
     *
     * @see https://datatables.net/reference/button/
     *
     * @var array
     */
    protected $datatableButtons = [];


    /**
     * Ajoute un bouton
     *
     * @param $text
     * @param $action
     * @return $this
     */
    public function addDatatableButton($text, $action)
    {
        $this->datatableButtons[] = ['text' => $text, 'action' => $action];
        return $this;
    }

    /**
     * Ajoute un lien au button datatable
     *
     * @param $text
     * @param $url
     * @return $this
     */
    public function addDatatableButtonLink($text, $url)
    {
        $this->addDatatableButton( $text, 'function() {window.open("'.$url.'")}');
        return $this;
    }

    /**
     * Ajoute un Bouton d'export
     *
     * @param $text
     * @param $url
     * @return $this
     */
    public function addDatatableButtonExport($text = 'Export CSV')
    {
        $this->addDatatableButtonLink($text, route('datatable.xport', ['token' => $this->getNenuphar()->getToken()]));
        return $this;
    }

    /**
     * Reset les filtres
     *
     * @param string $text
     * @return $this
     */
    public function addDatatableButtonReset($text = 'Reset')
    {
        $this->addDatatableButton($text, 'function() {jQuery("table#'.$this->getAttribute('id').'").dataTable().fnClearFilters();}');
        return $this;
    }


    /**
     * Return TRUE si au moins un bouton est ajouté
     *
     * @return bool
     */
    public function hasDatatableButtons()
    {
        return is_array($this->datatableButtons) && count($this->datatableButtons) > 0;
    }

    /**
     * Clear $buttons
     *
     * @return $this
     */
    public function clearDatatableButtons()
    {
        $this->datatableButtons = [];
        return $this;
    }


    /**
     * Getter for $datatabaseButtons
     *
     * @return array
     */
    public function getDatatableButtons()
    {
        return $this->datatableButtons;
    }


    /**
     * Setter for $search callable function
     *
     * @param $function
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

                $table->getSource()->where($field, 'LIKE', $query . '%');
            };
        }

        if (!is_callable($function)) {
            throw new InvalidArgumentException('Search function is not callable');
        }

        $this->search = $function;
        return $this;
    }

    /**
     * @return $this
     */
    public function removeSearch()
    {
        unset($this->search);
        return $this;
    }

    /**
     * @return bool
     */
    public function hasSearch()
    {
        return isset($this->search);
    }

    /**
     * Search
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
     * Set TRUE to $is_remote
     *
     * @return $this
     */
    public function enableRemote()
    {
        $this->is_remote = true;
        return $this;
    }

    /**
     * Set FALSE to $is_remote attribute
     *
     * @return $this
     */
    public function disableRemote()
    {
        $this->is_remote = false;
        return $this;
    }

    /**
     * Return TRUE is datatable ajax is enable
     *
     * @return mixed
     */
    public function isRemote()
    {
        return $this->is_remote;
    }


    /**
     * Set $is_datatable attribute
     *
     * @param $datatable
     * @return $this
     */
    public function enableDatatable()
    {
        $this->is_datatable = true;
        return $this;
    }

    /**
     * Set to FALSE $is_datatable
     *
     * @return $this
     */
    public function disableDatatable()
    {
        $this->is_datatable = false;
        return $this;
    }

    /**
     * Return TRUE if datatable is enabled
     *
     * @return mixed
     */
    public function isDatatable()
    {
        return $this->is_datatable;
    }

    /**
     * Return true if $is_datatable is set
     *
     * @return bool
     */
    public function hasDatatable()
    {
        return isset($this->is_datatable);
    }



    /**
     * Load a datable from a token
     *
     * @param $token
     * @return Table
     */
    static public function load($token = null, $processQuery = false)
    {
        // Recuperation de la table
        $table = static::loadFromToken($token);

        // Recuperation de sinformation de requete
        $query = $table->getNenuphar()->getExtras();

        // process query
        if ($processQuery && !empty($query)) {
            $columns = $query['columns'];
            $search = empty($query['search']) ? null : $query['search'];
            $order = empty($query['order']) ? null : $query['order'];
            $table->processQuery($columns, $search, $order);
        }

        return $table;
    }

    /**
     * Process a query
     *
     * @param $columns
     * @param null $search
     * @param null $order
     * @return $this
     */
    public function processQuery($columns, $search = null, $order = null)
    {
        // gestion des recherches
        foreach ($columns as $c) {
            if ($c['searchable'] == "true" && $c['search']['value'] != '') {
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
