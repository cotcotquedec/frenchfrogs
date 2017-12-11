<?php namespace FrenchFrogs\Laravel\Routing;


class UrlGenerator extends \Illuminate\Routing\UrlGenerator
{

    protected $query = [];


    /**
     * Clone URL
     *
     * @return bool
     */
    public function clone()
    {
        return clone $this;
    }


    /**
     * Setter for $query
     *
     * @param array $query
     * @return $this
     */
    public function withQuery(array $query)
    {
        $this->query = $query;
        return $this;
    }

    /**
     * Unset Query
     *
     * @return $this
     */
    public function removeQuery()
    {
        $this->query = [];
        return $this;
    }

    /**
     *
     *
     * @return bool
     */
    public function hasQuery()
    {
        return count($this->query) > 0;
    }


    /**
     * Insert params into query
     *
     * @param $url
     * @return string
     */
    protected function insertQuery($url)
    {

        // si des paramètre sont passé, on les intègre
        if ($this->hasQuery()) {
            $url .= (strpos('?', $url) === false ? '?' : '') . http_build_query($this->query);
        }

        return $url;
    }

    /**
     * Generate an absolute URL to the given path.
     *
     * @param  string  $path
     * @param  mixed  $extra
     * @param  bool  $secure
     * @return string
     */
    public function to($path, $extra = [], $secure = null)
    {
        $url = parent::to($path, $extra, $secure);
        return $this->insertQuery($url);
    }

    /**
     * Generate the URL to an application asset.
     *
     * @param  string  $path
     * @param  bool    $secure
     * @return string
     */
    public function asset($path, $secure = null)
    {
        $url = parent::asset($path, $secure);
        return $this->insertQuery($url);
    }


    /**
     * Get the URL to a named route.
     *
     * @param  string  $name
     * @param  mixed   $parameters
     * @param  bool  $absolute
     * @return string
     *
     * @throws \InvalidArgumentException
     */
    public function route($name, $parameters = [], $absolute = true)
    {
        $url = parent::route($name, $parameters, $absolute);
        return $this->insertQuery($url);
    }

    /**
     * Get the URL to a controller action.
     *
     * @param  string  $action
     * @param  mixed $parameters
     * @param  bool $absolute
     * @return string
     */
    public function action($action, $parameters = [], $absolute = true)
    {
        $url = parent::action($action, $parameters, $absolute);
        return $this->insertQuery($url);
    }
}