<?php namespace FrenchFrogs\App\Http\Controllers;


use FrenchFrogs\Table\Renderer\Remote;
use FrenchFrogs\Table\Table\Table;
use Illuminate\Routing\Controller as BaseController;


class TableController extends BaseController
{

    /**
     * GEstion ajax de datatable
     *
     * @param $token
     * @return \Illuminate\Http\JsonResponse
     */
    public function datatable($token)
    {

        try {
            $request = request();

            // chargement de l'objet
            $table = Table::load($token);

            // configuration de la navigation
            $table->setItemsPerPage($request->get('length'));
            $table->setPageFromItemsOffset($request->get('start'));
            $table->setRenderer(new Remote());

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
            $table->getNenuphar()->setExtras(compact('columns', 'order', 'search'));
            $table->getNenuphar()->register();

            return response()->json(['data' => $data, 'draw' => $request->get('draw'), 'recordsFiltered' => $table->getItemsTotal(), 'recordsTotal' => $table->getItemsTotal()]);

        } catch (\Exception $e) {
            //Si on catch une erreur on renvoi une reponse json avec le code 500
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }


    /**
     * Export CSV
     *
     * @param $token
     */
    public function export($token)
    {
        $table = Table::load($token, true);
        $table->setItemsPerPage(5000);
        return $table->toCsv();
    }


    /**
     *
     * Edition en remote de datatable
     *
     * @param $token
     */
    public function edit($token)
    {
        $request = request();
        $table = Table::load($token);
        return $table->getColumn($request->get('column'))->remoteProcess($request->get('id'), $request->get('value'));
    }

}