<?php namespace FrenchFrogs\App\Http\Controllers;

use FrenchFrogs\Form\Form\Form;
use Illuminate\Routing\Controller as BaseController;

class FormController extends BaseController
{


    /**
     *
     * @param $token
     */
    public function modal($token)
    {

        $form = Form::loadFromToken($token);

        try {
            $form->setDataFromRequest()->save() &&
            js()->success()->closeRemoteModal()->reloadDataTable();
        } catch (ValidationException $e) {
            js()->warning();
        } catch (\Throwable $e) {
            debugbar()->addThrowable($e);
            js()->error();
        }

        return response()->modal($form);
    }
}