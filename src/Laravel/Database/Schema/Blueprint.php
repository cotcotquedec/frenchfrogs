<?php namespace FrenchFrogs\Laravel\Database\Schema;


/**
 * Extension
 *
 * Class Blueprint
 * @package FrenchFrogs\Laravel\Database\Schema
 */
class Blueprint extends \Illuminate\Database\Schema\Blueprint
{
    /**
     * Create a new medium blob column on the table.
     *
     * @param  string  $column
     * @return \Illuminate\Support\Fluent
     */
    public function mediumBlob($column)
    {
        return $this->addColumn('mediumBlob', $column);
    }

    /**
     * Ajoute une reference
     *
     * @param $columns
     * @return \Illuminate\Support\Fluent
     */
    public function reference($columns)
    {
        $this->foreign($columns)->references('rid')->on('references');
        return $this->string($columns, 64);
    }

    /**
     * Ajoute une colonne id de type string
     *
     * @param string $column
     * @param int $size
     * @return mixed
     */
    public function sid($column = 'sid', $size = 32)
    {
        $column = $this->string($column, $size);
        return $column;
    }
}