<?php

// gestion de la navigation Ajax
Route::get('/ff/datatable/{token}', function ($token) {

    try {
        $request = request();

        // chargement de l'objet
        $table = FrenchFrogs\Table\Table\Table::load($token);

        // configuration de la navigation
        $table->setItemsPerPage(Input::get('length'));
        $table->setPageFromItemsOffset(Input::get('start'));
        $table->setRenderer(new FrenchFrogs\Table\Renderer\Remote());

        // gestion des reccherches
        foreach (request()->get('columns') as $c) {
            if ($c['searchable'] == "true" && $c['search']['value'] != '') {
                $table->getColumn($c['name'])->getStrainer()->call($table, $c['search']['value']);
            }
        }

        // gestion de la recherche globale
        $search = $request->get('search');
        if (!empty($search['value'])) {
            $table->search($search['value']);
        }

        // gestion du tri
        $order = $request->get('order');
        if (!empty($order)) {

            if ($table->isSourceQueryBuilder()) {
                $table->getSource()->orders = [];
            }

            foreach ($order as $o) {
                extract($o);
                $table->getColumnByIndex($column)->order($dir);
            }
        }

        // recuperation des données
        $data = [];
        foreach ($table->render() as $row) {
            $data[] = array_values($row);
        }

        return response()->json(['data' => $data, 'draw' => Input::get('draw'), 'recordsFiltered' => $table->getItemsTotal(), 'recordsTotal' => $table->getItemsTotal()]);

    } catch (\Exception $e) {
        //Si on catch une erreur on renvoi une reponse json avec le code 500
        return response()->json(['error' => $e->getMessage()], 500);
    }

})->name('datatable');


/**
 * Gestion de l'export CSV
 */
Route::get('/ff/datatable/{token}/export', function ($token) {
    $table = FrenchFrogs\Table\Table\Table::load($token);
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


Route::get('/ff/media/{id}', 'FrenchFrogs\App\Http\Controllers\MediaController@show')->name('media-show');
Route::get('/ff/media/dl/{id}', 'FrenchFrogs\App\Http\Controllers\MediaController@download')->name('media-dl');

