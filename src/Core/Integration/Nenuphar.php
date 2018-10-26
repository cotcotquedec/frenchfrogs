<?php namespace FrenchFrogs\Core\Integration;


trait Nenuphar
{

    /**
     * @var \FrenchFrogs\Core\Nenuphar
     */
    protected $nenuphar;

    /**
     * @param \FrenchFrogs\Core\Nenuphar|string $nenuphar
     * @param null $method
     * @param array $params
     * @param string $interpreter
     * @return $this
     */
    public function setNenuphar($class, $method = null, $params = [], $interpreter = 'controller')
    {
        // Formatage des parmaetre en nenuphar
        if ($class instanceof Nenuphar) {
            $nenuphar = $class;
        } else {
            $nenuphar = new \FrenchFrogs\Core\Nenuphar(...func_get_args());
        }

        $this->nenuphar = $nenuphar;
        return $this;
    }

    /**
     *
     * @return \FrenchFrogs\Core\Nenuphar
     */
    public function getNenuphar()
    {
        return $this->nenuphar;
    }

    /**
     * @param $token
     * @throws \Exception
     * return static
     */
    static public function loadFromToken($token)
    {
        // Recuperation de la table
        $nenuphar = \FrenchFrogs\Core\Nenuphar::fromToken($token);

        $class = $nenuphar->execute();
        if (!($class instanceof static)) {
            throw new \Exception('Le token "' . $token .'" ne renvoit pas un objet valide');
        }

        // attribution du token
        $class->setNenuphar($nenuphar);

        //
        return $class;
    }
}