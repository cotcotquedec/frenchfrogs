<?php

/**
 * Return Reference for the collection
 *
 * @return \FrenchFrogs\Models\Reference
 */
function ref($collection, $force_refresh = false) {

    // recuperation de la collection
    $reference = \FrenchFrogs\Models\Reference::getInstance($collection);

    // on rafraichie le cache si demandé
    if ($force_refresh) {
        $reference->clear()->getData();
    }

    return $reference;
}
