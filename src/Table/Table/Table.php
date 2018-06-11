<?php namespace FrenchFrogs\Table\Table;


use FrenchFrogs\Core;
use FrenchFrogs\Table\Column;
use FrenchFrogs\Table\Renderer;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Database\Query\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use InvalidArgumentException;


/**
 * Table polliwog
 *
 * Default table is build with a bootstrap support
 *
 * Class Table
 * @package FrenchFrogs\Table
 */
class Table
{

    use Core\Renderer;
    use Core\Filterer;
    use \FrenchFrogs\Html\Html;
    use Core\Panel;
    use Pagination;
    use Bootstrap;
    use Datatable, Core\Integration\Nenuphar;
    use Columns;
    use Export;

    /**
     *
     * Data for the table
     *
     * @var \Iterator $rows
     */
    protected $rows = [];


    /**
     * Source data
     *
     * @var
     */
    protected $source;

    /**
     * If false, footer will not be render
     *
     * @var bool
     */
    protected $has_footer = true;

    /**
     * Enable Json decode on value
     *
     * @var array
     */
    protected $jsonField = [];

    /**
     * Contain the name of the id fiels in data
     *
     * @var
     */
    protected $idField;


    /**
     *
     * @var function
     */
    protected $setup;


    /**
     *
     * @param $function
     * @return $this
     */
    public function setSetup($function)
    {
        throw_unless(is_callable($function), new \Exception('Le setup doit être une fonction'));
        $this->setup = $function;
        return $this;
    }

    /**
     * @return function
     */
    public function getSetup()
    {
        return $this->setup;
    }

    /**
     *
     * @return bool
     */
    public function hasSetup()
    {
        return !empty($this->setup);
    }

    /**
     * Return TRUE if a column has a strainer
     *
     * @return bool
     */
    public function hasStrainer()
    {
        foreach ($this->getColumns() as $column) {
            if ($column->hasStrainer()) {
                return true;
            }
        }
        return false;
    }


    /**
     * Return TRUE if $idField is set
     *
     * @return bool
     */
    public function hasIdField()
    {
        return isset($this->idField);
    }

    /**
     * Constructor
     *
     * @param string $url
     * @param string $method
     */
    public function __construct()
    {
        /*
         * Default configuration
         */
        if (!$this->hasRenderer()) {
            $class = ff()->get('table.renderer');
            $this->setRenderer(new $class);
        }

        if (!$this->hasFilterer()) {
            $class = ff()->get('table.filterer');
            $this->setFilterer(new $class);
        }

        if (!$this->hasUrl()) {
            $this->setUrl(request()->url());
        }

        $this->enableBordered();

        // if method "init" exist, we call it.
        if (method_exists($this, 'init')) {
            call_user_func_array([$this, 'init'], func_get_args());
        } elseif (func_num_args() == 1) {
            $this->setSource(func_get_arg(0));
        }

        // Force id html attribute
        if (!$this->hasAttribute('id')) {
            $this->addAttribute('id', 'table-' . rand());
        }
    }


    /**
     * Set all the rows container
     *
     * @param \Iterator $rows
     * @return $this
     */
    public function setRows(\Iterator $rows)
    {
        $this->rows = $rows;
        return $this;
    }


    /**
     * return all the rows container
     *
     * @return \Iterator
     */
    public function getRows()
    {
        return $this->rows;
    }


    /**l
     * Clear all the rows container
     *l
     * @return $this
     */
    public function clearRows()
    {
        $this->rows = new \ArrayIterator();
        return $this;
    }


    /**
     *
     *
     * @param $source
     * @return $this
     */
    public function setSource($source)
    {

        if (is_object($source)) {
            while (method_exists($source, 'getQuery')) {
                $source = $source->getQuery();
            }
        }

        $this->source = $source;
        return $this;
    }


    /**
     * Getter for $source attribute
     *
     * @return array
     */
    public function getSource()
    {
        return $this->source;
    }

    /**
     * Return TRUE if $source attribute is set
     *
     * @return bool
     */
    public function hasSource()
    {
        return isset($this->source);
    }

    /**
     * Extract rows from $source attribute
     *
     * @return $this
     */
    protected function extractRows()
    {
        $source = $this->source;

        // Laravel query builder case
        if ($this->isSourceQueryBuilder()) {
            /** @var $source \Illuminate\Database\Query\Builder */

            $count = query(raw("({$source->toSql()}) as a"), [raw('COUNT(*) as _num_rows')], $source->getConnection()->getName())->mergeBindings($source)->first();
            a($count); // cast

            $this->itemsTotal = isset($count['_num_rows']) ? $count['_num_rows'] : null;
            $source = $source->skip($this->getItemsOffset())->take($this->getItemsPerPage())->get();

            // Compatibilité avec laravel  5.3
            a($source); // cast

            $source = new \ArrayIterator($source);

            // Array case
        } elseif (is_array($source)) {
            $this->itemsTotal = count($source);
            $source = array_slice($source, $this->getItemsOffset(), $this->getItemsPerPage());
            $source = new \ArrayIterator($source);
        }

        /**@var $source \Iterator */
        if (!($source instanceof \Iterator)) {
            throw new \InvalidArgumentException("Source must be an array or an Iterator : " . get_class($source));
        }


        if (!is_null($source)) {
            $this->setRows($source);

            if ($this->hasJsonField()) {
                $this->extractJson();
            }
        }

        return $this;
    }

    /**
     * Debug first row
     *
     * @return mixed
     */
    public function dd()
    {
        dd($this->extractRows()->getRows()->current());
    }

    /**
     * Return true if the source is an instance of \Illuminate\Database\Query\Builder
     *
     * @return bool
     */
    public function isSourceQueryBuilder()
    {
        return is_object($this->getSource()) && ($this->getSource() instanceof Builder);
    }


    /**
     * Set $has_footer attribute to TRUE
     *
     * @return $this
     */
    public function enableFooter()
    {
        $this->has_footer = true;
        return $this;
    }

    /**
     * Set $has_footer attribute to FALSE
     *
     * @return $this
     */
    public function disableFooter()
    {
        $this->has_footer = false;
        return $this;
    }


    /**
     * return TRUE if $has_footer is set tio TRUE
     *
     * @return bool
     */
    public function hasFooter()
    {
        return $this->has_footer;
    }



    /**
     * *******************
     * RENDERER
     * *******************
     */

    /**
     * Render polliwog
     *
     * @return mixed|string
     */
    public function render()
    {

        $render = '';
        try {
            $this->extractRows();
            $render = $this->getRenderer()->render('table', $this);
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
     * Add json field to decode
     *
     * @param $field
     */
    public function addJsonField($field)
    {
        $this->jsonField[] = $field;
        return $this;
    }

    /**
     * Remove $field from $jsonField
     *
     * @param $field
     * @return $this
     */
    public function removeJsonField($field)
    {

        $i = array_search($field, $this->jsonField);

        if ($i !== false) {
            unset($this->jsonField[$i]);
        }

        return $this;
    }


    /**
     * Setter for $jsonField
     *
     * @param array $fields
     * @return $this
     */
    public function setJsonFields(array $fields)
    {
        $this->jsonField = $fields;
        return $this;
    }

    /**
     * Getter for jsonField
     *
     * @return array
     */
    public function getJsonFields()
    {
        return $this->jsonField;
    }

    /**
     * Return TRUE if a json field is set at least
     *
     * @return bool
     */
    public function hasJsonField()
    {
        return count($this->jsonField) > 0;
    }

    /**
     * Extract json data
     *
     * @return $this
     */
    public function extractJson()
    {
        foreach ($this->getRows() as &$row) {

            foreach ($this->getJsonFields() as $field) {
                if (isset($row[$field])) {

                    $data = json_decode($row[$field], JSON_OBJECT_AS_ARRAY);

                    foreach ((array)$data as $k => $v) {
                        $row[sprintf('%s.%s', $field, $k)] = $v;
                    }
                }
            }
        }
        return $this;
    }

    /**
     * Setter for $idField
     *
     * @param $field
     * @return $this
     */
    public function setIdField($field)
    {
        $this->idField = $field;
        return $this;
    }

    /**
     * Getter for $idField
     *
     * @return mixed
     */
    public function getIdField()
    {
        throw_if(is_null($this->idField), 'La propriété idField n\'a pas été setté pour le table');
        return $this->idField;
    }

    /**
     * Unset $idField
     *
     * @return $this
     */
    public function removeIdField()
    {
        unset($this->idField);
        return $this;
    }

    /**
     * @param Request $request
     * @return $this
     */
    public function processRequest(Request $request = null)
    {
        // Paramètre par default
        $request = $request ?: request();


        switch($request->getMethod()) {
            case 'POST':
                // configuration de la navigation
                $this->setRenderer(new Renderer\Remote());
                $this->setItemsPerPage($request->get('length'));
                $this->setPageFromItemsOffset($request->get('start'));

                $columns = $request->get('columns');
                $search = $request->get('search');
                $order = $request->get('order');

                $this->processQuery($columns, $search, $order);
                break;

            case 'PUT':
                // Inscription des champs remote
                $this->setRenderer(new Renderer\Js());
                $this->getColumn($request->get('_column'))
                    ->remoteProcess($request->get('_id'), $request->get('_value', false));
                break;

            default :
                $this->hasSetup() && call_user_func($this->setup, $this);
        }


        return $this;
    }
}
