<?php

namespace FrenchFrogs\Laravel\Database\Schema;

/**
 * Extension.
 *
 * Class Blueprint
 */
class Blueprint extends \Illuminate\Database\Schema\Blueprint
{
    /**
     * Ajoute une column de type binary UUID.
     *
     * @param string $column
     *
     * @return \Illuminate\Support\Fluent
     */
    public function binaryUuid($column = 'uuid', $primary = true)
    {
        $column = $this->addColumn('binaryuuid', $column, ['length' => 16]);

        // gestion de la clé primaire
        if ($primary) {
            $column->primary();
        }

        return $column;
    }

    /**
     * Ajoute une colonne id de type string.
     *
     * @param string $column
     * @param int    $size
     *
     * @return mixed
     */
    public function stringId($column = 'sid', $size = 32, $primary = true)
    {
        $column = $this->string($column, $size);

        // gestion de la clé primaire
        if ($primary) {
            $column->primary();
        }

        return $column;
    }
}
