<?php

// gestion de la navigation Ajax
Route::get('/ff/datatable/{token}', function ($token) {

    try {
        $request = request();

        // chargement de l'objet
        $table = FrenchFrogs\Table\Table\Table::load($token);

        // configuration de la navigation
        $table->setItemsPerPage($request->get('length'));
        $table->setPageFromItemsOffset($request->get('start'));
        $table->setRenderer(new FrenchFrogs\Table\Renderer\Remote());

        $columns = $request->get('columns');
        $search = $request->get('search');
        $order = $request->get('order');
        $table->processQuery($columns, $search, $order);

        // recuperation des données
        $data = [];
        foreach ($table->render() as $row) {
            $data[] = array_values($row);
        }

        // on sauvegarde la recherche
        $table->save(compact('columns', 'order', 'search'));

        return response()->json(['data' => $data, 'draw' => $request->get('draw'), 'recordsFiltered' => $table->getItemsTotal(), 'recordsTotal' => $table->getItemsTotal()]);

    } catch (\Exception $e) {
        //Si on catch une erreur on renvoi une reponse json avec le code 500
        return response()->json(['error' => $e->getMessage()], 500);
    }

})->name('datatable');


/**
 * Gestion de l'export CSV
 */
Route::get('/ff/datatable/{token}/export', function ($token) {
    $table = FrenchFrogs\Table\Table\Table::load($token, true);
    $table->setItemsPerPage(5000);
    $table->toCsv();
})->name('datatable-export');


/**
 * Gestion de l'edition en remote
 *
 */
Route::post('/ff/datatable/{token}', function ($token) {
    $request = request();
    $table = FrenchFrogs\Table\Table\Table::load($token);
    return $table->getColumn($request->get('column'))->remoteProcess($request->get('id'), $request->get('value'));
});


//Route::get('/ff/media/{id}', '\FrenchFrogs\App\Http\Controllers\MediaController@show')->name('media-show');
//Route::get('/ff/media/dl/{id}', '\FrenchFrogs\App\Http\Controllers\MediaController@download')->name('media-dl');

