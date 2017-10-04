<?php namespace FrenchFrogs\Maker;


trait Docblock
{
    /**
     *
     *
     * @var string
     */
    protected $summary = '';


    /**
     * Description
     *
     * @var string
     */
    protected $description = '';

    /**
     * Tags
     *
     * @var array
     */
    protected $tags = [];


    /**
     * Annotations
     *
     * @var array
     */
    protected $annotations = [];

    /**
     * Getter for $summary
     *
     * @return mixed
     */
    public function getSummary()
    {
        return $this->summary;
    }

    /**
     * Setter for $summary
     *
     * @param $summary
     * @return $this
     */
    public function setSummary($summary)
    {
        $this->summary = $summary;
        return $this;
    }

    /**
     * Setter for $description
     *
     * @param $description
     * @return $this
     */
    public function setDescription($description)
    {
        $this->description = strval($description);
        return $this;
    }

    /**
     * Getter for $description
     *
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * Setter for $tags
     *
     * @param array $tags
     * @return $this
     */
    public function setTags(array $tags)
    {
        $this->tags = $tags;
        return $this;
    }


    /**
     * Clear all tags
     *
     * @return $this
     */
    public function clearTags()
    {
        $this->tags = [];
        return $this;
    }

    /**
     * Add a tag
     *
     * @param $name
     * @param $value
     * @return $this
     */
    public function addTag($name, $value)
    {
        $this->tags[] = [$name,$value];
        return $this;
    }

    /**
     * Add a tag type param
     *
     * @param $name
     * @param null $type
     * @param $description
     * @return $this
     */
    public function addTagParam($name, $type = null, $description = null)
    {
        // construction du tag
        $tag  = '';

        if (!is_null($type)) {
            $tag .=  $type . ' ';
        }

        $tag .= $name;

        if (!is_null($description)) {
            $tag .= ' ' . $description;
        }

        $this->addTag('param', $tag);

        return $this;
    }

    /**
     * Add a tag type param
     *
     * @param $name
     * @param null $type
     * @param $description
     * @return $this
     */
    public function addTagProperty($name, $type = null, $description = null)
    {
        // construction du tag
        $tag  = '';

        if (!is_null($type)) {
            $tag .=  $type . ' ';
        }

        $tag .= $name;

        if (!is_null($description)) {
            $tag .= ' ' . $description;
        }

        $this->addTag('property', $tag);

        return $this;
    }


    /**
     *
     * @param $name
     * @param $type
     * @param null $description
     * @return $this
     */
    public function addTagVar($type, $description = null)
    {
        // construction du tag
        $tag  = '';
        $tag .=  $type . ' ';

        if (!is_null($description)) {
            $tag .= ' ' . $description;
        }

        $this->addTag('var', $tag);

        return $this;
    }


    /**
     * Getter for tags
     *
     * @return array
     */
    public function getTags()
    {
        return $this->tags;
    }

    /**
     * Getter pour un tag avec un notion de default
     *
     * @param $name
     * @param null $default
     * @return mixed
     */
    public function getTag($name, $default = null)
    {
        // si le tag exists on le renvoie

        foreach ($this->tags as $tag) {

            // On verifie que l'on a bien un tabvleau avec de valeurs
            if (is_array($tag) && count($tag) > 1) {
                $n = array_shift($tag);

                // si c'est le bon tag, on le renvoie
                if ($n == $name) {
                    return implode(' ', $tag);
                }
            }
        }

        return $default;
    }


    /**
     * Setter for $annotations
     *
     * @param array $annotations
     * @return $this
     */
    public function setAnnotation(array $annotations)
    {
        $this->annotations = $annotations;
        return $this;
    }


    /**
     * Clear all Annotations
     *
     * @return $this
     */
    public function clearAnnotations()
    {
        $this->annotations = [];
        return $this;
    }

    /**
     * Add a tag
     *
     * @param $name
     * @param $value
     */
    public function addAnnotation($value)
    {
        $this->annotations[] = $value;
    }

    /**
     * Getter for tags
     *
     * @return array
     */
    public function getAnnotations()
    {
        return $this->annotations;
    }

}