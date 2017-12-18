<?php namespace FrenchFrogs\Laravel\Database\Schema;


/**
 * Extension
 *
 * Class Blueprint
 * @package FrenchFrogs\Laravel\Database\Schema
 */
class Blueprint extends \Illuminate\Database\Schema\Blueprint
{

//    /**
//     * Ajoute une column de type binary UUID
//     *
//     * @param string $column
//     * @return \Illuminate\Support\Fluent
//     */
//    public function binaryUuid($column = 'uuid', $primary = true)
//    {
//        $column = $this->addColumn('binaryuuid', $column, ['length' => 16]);
//
//        // gestion de la clé primaire
//        if ($primary) {
//            $column->primary();
//        }
//
//        return $column;
//    }


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