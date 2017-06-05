<?php namespace FrenchFrogs\PCRE;


class PCRE
{

    /**
     * @var
     */
    protected $pattern;

    /**
     * Resultat du dernier match
     *
     * @var
     */
    protected $match;


    /**
     * PCRE constructor.
     * @param $pattern
     */
    public function __construct($pattern)
    {
        $this->pattern = $pattern;
    }

    /**
     *
     *
     * @param $value
     * @return Result
     */
    public function match($value)
    {
        $match = [];

        preg_match($this->pattern, $value, $match);

        return new Result(collect($match));
    }


    /**
     * @param $pattern
     * @return static
     */
    static function fromPattern($pattern)
    {
        $class = new static($pattern);
        return $class;
    }

}