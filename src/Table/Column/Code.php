<?php namespace FrenchFrogs\Table\Column;


class Code extends Text
{

    const LANGUAGE_HTML = 'html';
    const LANGUAGE_CSS = 'css';
    const LANGUAGE_JSON = 'json';
    const LANGUAGE_PHP = 'php';
    const LANGUAGE_JS = 'js';
    const LANGUAGE_DISABLE = 'nohighlight';

    /**
     * @return Code
     */
    public function removeLanguage()
    {
        return $this->setLanguage(static::LANGUAGE_DISABLE);
    }

    /**
     * Getter for $languague
     *
     * @return string
     */
    public function getLanguage()
    {
        return $this->language;
    }

    /**
     * Setter for $langaguage
     *
     * @param $language
     * @return $this
     */
    public function setLanguage($language)
    {
        $this->language = $language;
        return $this;
    }

    /**
     * @return Code
     */
    public function setLanguageAsHtml()
    {
        return $this->setLanguage(static::LANGUAGE_HTML);
    }

    /**
     * @return Code
     */
    public function setLanguageAsJson()
    {
        return $this->setLanguage(static::LANGUAGE_JSON);
    }


    /**
     * @return Code
     */
    public function setLanguageAsPhp()
    {
        return $this->setLanguage(static::LANGUAGE_PHP);
    }


    /**
     * @return Code
     */
    public function setLanguageAsJs()
    {
        return $this->setLanguage(static::LANGUAGE_JS);
    }

    /**
     * @return Code
     */
    public function setLanguageAsCss()
    {
        return $this->setLanguage(static::LANGUAGE_CSS);
    }

    public function __construct($name, $label = '', array $attr = [])
    {
        parent::__construct($name, $label, $attr);
        $this->addClass('ff-highlight');
    }


    public function getValue($row)
    {
        $value =  parent::getValue($row);

        if ($this->getLanguage() == static::LANGUAGE_JSON) {
            $value = json_decode($value);
            $value = json_encode($value, JSON_PRETTY_PRINT);
        }

        return $value;
    }

    /**
     *
     *
     * @return string
     */
    public function render(array $row)
    {
        $render = '';
        try {
            $this->addClass($this->getLanguage());
            $render = $this->getRenderer()->render('code', $this, $row);
        } catch(\Exception $e){
            dd($e->getMessage());
        }

        return $render;
    }
}