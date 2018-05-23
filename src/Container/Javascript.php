<?php namespace FrenchFrogs\Container;


use MatthiasMullie\Minify\JS as MiniJs;

/**
 * Javascript container
 *
 * Class Javascript
 * @package FrenchFrogs\Container
 */
class Javascript extends Container
{

    const NAMESPACE_DEFAULT = 'onload';

    use Minify;

    protected $files = [];


    /**
     * Add link
     *
     * @param $href
     * @param string $rel
     * @param string $type
     * @return Javascript
     */
    public function file($href)
    {
        $this->files[] = $href;
        return $this;
    }

    /**
     * Ecnode un paramètre js
     *
     * @param $var
     * @return mixed
     */
    protected function encode($var)
    {

        $functions = [];

        // on force un niveau supérieur
        $var = [$var];

        // bind toutes les functions
        array_walk_recursive($var, function(&$item, $key) use (&$functions){
            if (substr($item,0,8) == 'function') {
                $index = '###___' . count($functions) . '___###';
                $functions['"' . $index . '"'] = $item;
                $item = $index;
            }
        });

        // Encodage
        $var = json_encode($var[0], JSON_PRETTY_PRINT );

        // Rebind des functions
        $var = str_replace(array_keys($functions), array_values($functions), $var);

        return $var;
    }

    /**
     * Build a jquery call javascript code
     *
     * @param $selector
     * @param $function
     * @param ...$params
     * @return string
     */
    public function build($selector, $function, ...$params)
    {
        $attributes = [];


        foreach($params as $p) {
            $attributes[] = $this->encode($p);
        }

        //n concatenation du json
        $attributes =  implode(',', $attributes);

        // gestion des functions
        $attributes = preg_replace('#\"(function\([^\{]+{.*\})\",#', '$1,', $attributes);

        return sprintf('$("%s").%s(%s);', $selector, $function, $attributes);
    }

    /**
     * Append build javascript to $container attribute
     *
     * @param $selector
     * @param $function
     * @param ...$params
     * @return Javascript
     */
    public function appendJs($selector, $function, ...$params)
    {
        array_unshift($params, $selector, $function);
        $this->append( call_user_func_array([$this, 'build'], $params));
        return $this;
    }

    /**
     * Prepend build javascript to $container attribute
     *
     * @param $selector
     * @param $function
     * @param ...$params
     * @return Javascript
     */
    public function prependJs($selector, $function, ...$params)
    {
        array_unshift($params, $selector, $function);
        $this->prepend( call_user_func_array([$this, 'build'], $params));
        return $this;
    }

    /**
     * Add alert() javascript function to the container
     *
     * @param $message
     * @return Javascript
     */
    public function alert($message)
    {
        $this->append( sprintf('alert("%s");', $message));
        return $this;
    }

    /**
     * Add console.log() javascript function to the container
     *
     * @param $message
     * @return Javascript
     */
    public function log($message)
    {
        $this->append( sprintf('console.log("%s");', $message));
        return $this;
    }


    /**
     * Add toastr warning message
     *
     * @param $body
     * @param string $title
     * @return Javascript
     */
    public function warning($body = '', $title = '')
    {
        $body = empty($body) ?  ff()->get('toastr.warning') : $body;
        $this->append( sprintf('toastr.warning("%s", "%s");', $body, $title));
        return $this;
    }

    /**
     * Add toastr success message
     *
     * @param $body
     * @param string $title
     * @return Javascript
     */
    public function success($body = '', $title = '')
    {
        $body = empty($body) ?  ff()->get('toastr.success') : $body;
        $this->append( sprintf('toastr.success("%s", "%s");', $body, $title));
        return $this;
    }


    /**
     * Add toastr success message
     *
     * @param $body
     * @param string $title
     * @return Javascript
     */
    public function error($body = '', $title = '')
    {
        $body = empty($body) ?  ff()->get('toastr.error') : $body;
        $this->append(sprintf('toastr.error("%s", "%s");', addslashes($body), $title));
        return $this;
    }

    /**
     * Close the remote modal
     *
     * @return Javascript
     */
    public function closeRemoteModal()
    {
        $this->appendJs('#modal-remote', 'modal', 'hide');
        return $this;
    }

    /**
     * reload the page
     *
     * @return Javascript
     */
    public function reload()
    {
        $this->append( 'window.location.reload()');
        return $this;
    }

    /**
     * Redirection javascript
     *
     * @param $url
     * @return Javascript
     */
    public function redirect($url)
    {
        $this->append( 'window.location.href = "'.$url.'"');
        return $this;
    }

    /**
     * ReloadAjaxDatatable
     *
     * @param bool|false $resetPaging
     * @return Javascript
     */
    public function reloadDataTable($resetPaging = false)
    {
        $this->append( 'jQuery(".datatable-remote").DataTable().ajax.reload(null, '. ($resetPaging ?  'true' : 'false') .');');
        return $this;
    }


    /**
     * Clear the $container attribute
     *
     * @return Javascript
     */
    public function clear()
    {
        $this->files = [];
        return parent::clear();
    }

    /**
     *
     *
     * @return string
     */
    public function __toString()
    {
        $result = '';
        try {

            // If we want to minify
            if ($this->isMinify()) {

                $hash = '';
                $contents = [];

                // TRaitement des fichiers
                foreach ($this->files as $c) {
                    // scheme case
                    if (preg_match('#^//.+$#', $c)) {
                        $c = 'http:' . $c;
                        $contents[] = ['remote', $c];
                        $hash .= md5($c);

                        // url case
                    } elseif (preg_match('#^https?://.+$#', $c)) {
                        $contents[] = ['remote', $c];
                        $hash .= md5($c);

                        // local file
                    } else {
                        $c = public_path($c);
                        $hash .= md5_file($c);
                        $contents[] = ['local', $c];
                    }
                }

                // manage remote or local file
                foreach ($this->container as $content) {
                    $hash .= md5($c);
                    $contents[] = ['inline', $c];
                }

                // destination file
                $target = public_path($this->getTargetPath());
                if (substr($target, -1) != '/') {
                    $target .= '/';
                }
                $target .= md5($hash) . '.js';

                // add css to minifier
                if (!file_exists($target)) {

                    $minifier = new MiniJs();

                    // Remote file management
                    foreach($contents as $content) {

                        list($t, $c) = $content;

                        // we get remote file content
                        if ($t == 'remote') {
                            $c = file_get_contents($c);
                        }

                        $minifier->add($c);
                    }

                    // minify
                    $minifier->minify($target);
                }

                // set $file
                $result .= html('script',
                        [
                            'src' => str_replace(public_path(), '', $target),
                            'type' => 'text/javascript',
                        ]
                    ) . PHP_EOL;

            } else {


                foreach ($this->files as $c) {
                    $result .= html('script',
                            [
                                'src' => $c,
                                'type' => 'text/javascript',
                            ]
                        ) . PHP_EOL;
                }


                foreach ($this->container as $content) {
                    $result .= $content . $this->getGlue();
                }
            }

        } catch(\Exception $e) {

            $result = '<!--' . PHP_EOL . 'Error on js generation' . PHP_EOL;

            // stack trace if in debug mode
            if (is_debug()) {
                $result .= $e->getMessage() . ' : ' . PHP_EOL . $e->getTraceAsString() . PHP_EOL;
            }

            $result .= '-->';
        }

        return $result;
    }


    /**
     * Envoie un event analytics
     *
     * @link https://developers.google.com/analytics/devguides/collection/analyticsjs/events
     *
     * @param $category
     * @param $action
     * @param $label
     * @return Javascript
     */
    public function gaEvent($category, $action, $label)
    {
        return $this->append( sprintf("ga('send', 'event', '%s', '%s', '%s');", $category, $action, $label));
    }


    /**
     * Envoie un event analytics
     *
     * @link https://developers.google.com/analytics/devguides/collection/analyticsjs/events
     *
     * @param $category
     * @param $action
     * @param $label
     * @return Javascript
     */
    public function gaView($page = false)
    {
        return $this->append( $page ? sprintf("ga('send', 'pageview', '%s');", $page) : "ga('send', 'pageview')");
    }


    /**
     *  Set un utilisateurt analytics
     * @param $user
     * @return Javascript
     */
    public function gaSetUser($user)
    {
        return $this->append( sprintf("ga('set', 'userId', '%s');", $user));
    }
}