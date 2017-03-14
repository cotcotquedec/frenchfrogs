<?php
/**
 * Ce fichier a pour but d'aider à l'autocompletion dans PhpStorm,
 *
 * Il rajoute Les macro et autre function qui ne sont pas active via l'autocompletion
 */

namespace  {
    exit("This file should not be included, only analyzed by your IDE");
}


namespace Illuminate\Database\Query {

    /**
     * Class Builder
     * @package Illuminate\Database\Query
     */
    class Builder
    {

        /**
         * Ajoute une colonne : SUM($columns) as $alias
         *
         * @param $expression
         * @return $this
         */
        public function addSelectSum($expression) {
            return $this;
        }


        /**
         * Ajoute une colonne : COUNT($columns) as $alias
         *
         * @param $expression
         * @return $this
         */
        public function addSelectCount($expression) {
            return $this;
        }

        /**
         * Ajoute une colonne : COUNT($columns) as $alias
         *
         * @param $expression
         * @return $this
         */
        public function addSelectHex($expression) {
            return $this;
        }


        /**
         * Effectue une joiture sur un sou requete
         *
         * @param $column
         * @param null $alias
         * @return $this
         */
        public function leftJoinQuery(Builder $sub, $alias, $first, $operator = null, $second = null) {
            return $this;
        }


        /**
         * Debug une requete
         *
         */
        public function dd(){}
    }
}