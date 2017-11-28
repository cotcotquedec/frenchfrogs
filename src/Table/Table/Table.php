<?php namespace FrenchFrogs\Table\Table;


use FrenchFrogs\Core;
use FrenchFrogs\Table\Column;
use FrenchFrogs\Table\Renderer;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Database\Query\Builder;
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
    use Datatable;
    use Columns;
    use Export;

    /**
     *
     * Data for the table
     *
     * @var Collection $rows
     */
    protected $rows;


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
     * Return TRUE if a column has a strainer
     *
     * @return bool
     */
    public function hasStrainer()
    {
        foreach($this->getColumns() as $column) {
            if ( $column->hasStrainer()) {
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
            $class = configurator()->get('table.renderer.class');
            $this->setRenderer(new $class);
        }

        if (!$this->hasFilterer()) {
            $class = configurator()->get('table.filterer.class');
            $this->setFilterer(new $class);
        }

        if (!$this->hasUrl()){
            $this->setUrl(request()->url());
        }

        $this->enableBordered();

        // if method "init" exist, we call it.
        if (method_exists($this, 'init')) {
            call_user_func_array([$this, 'init'], func_get_args());
        } elseif(func_num_args() == 1) {
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
     * @param Collection  $rows
     * @return $this
     */
    public function setRows(Collection $rows)
    {
        $this->rows = $rows;
        return $this;
    }


    /**
     * return all the rows container
     *
     * @return Collection
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
        $this->rows = collect([]);
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
            while(method_exists($source, 'getQuery')) {
                $source = $source->getQuery();
            }
        } elseif (is_array($source)) {
            $source = collect($source);
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
        if( $this->isSourceQueryBuilder())  {
            /** @var $source \Illuminate\Database\Query\Builder */

            $count = query(raw("({$source->toSql()}) as a"), [raw('COUNT(*) as _num_rows')], $source->getConnection()->getName())->mergeBindings($source)->first();
            $this->itemsTotal = object_get($count, '_num_rows');
            $source = $source->skip($this->getItemsOffset())->take($this->getItemsPerPage())->get();

        } elseif($source instanceof Collection) {
            $this->itemsTotal = $source->count();
            $source = $source->slice($this->getItemsOffset(), $this->getItemsPerPage());
        }



        /**@var $source \Iterator */
        if (!($source instanceof Collection)) {
            throw new \InvalidArgumentException("Source must be a Collection : " . get_class($source));
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
        } catch(\Exception $e){
            \Debugbar::addThrowable($e);
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
    public  function removeJsonField($field)
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

        $this->getRows()->transform(function($row) {
            a($row);
            foreach($this->getJsonFields() as $field) {
                if (isset($row[$field])) {

                    $data = json_decode($row[$field], JSON_OBJECT_AS_ARRAY);

                    foreach((array) $data as $k => $v) {
                        $row[sprintf('%s.%s', $field, $k)] = $v;
                    }
                }
            }

            return $row;
        });
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
}