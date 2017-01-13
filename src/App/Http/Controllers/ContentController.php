<?php namespace FrenchFrogs\App\Http\Controllers;


use App\Http\Controllers\Controller;
use FrenchFrogs\App\Models\Business\Mail;
use FrenchFrogs\Core\FrenchFrogsController;
use Illuminate\Database\Query\JoinClause;
use Illuminate\Http\Request;
use App\Http\Requests;
use Models\Acl\Inside as Acl;


/**
 *
 *
 */
class ContentController extends Controller
{

    use FrenchFrogsController;

    /**
     * Liste des candidats
     *
     *
     */
    public static function content()
    {

        // QUERY
        $query = \query('content as c', [
            raw('HEX(uuid) as  uuid'),
            'content_index',
            'content',
            'is_published',
            'c.created_at',
        ]);

        $table = \table($query);
        $table->setConstructor(static::class, __FUNCTION__)->enableRemote()->enableDatatable();
        $table->useDefaultPanel('Contenu')
            ->getPanel()
            ->addButton('add', 'Ajouter', action_url(static::class, 'postContent'))
            ->enableRemote()
            ->setOptionAsPrimary();

        // COLMUMN
        $table->addText('content_index', 'index')->setStrainerText('content_index');
        $table->addText('content', 'Contenu')->setStrainerText('content');
        $table->addBoolean('is_published', 'Publié?')->setStrainerBoolean('is_published');
        $table->addDate('created_at')->setOrder('created_at');

        if (!request()->isXmlHttpRequest()) {
            $table->getColumn('created_at')->order('desc');
        }

        // ACTION
        $action = $table->addContainer('action', 'Action')->setWidth(160);
        $action->addButtonEdit(action_url(static::class, 'postContent', '%s'), 'uuid');

        return $table;
    }

    /**
     * Gestion des email
     *
     */
    function getIndex()
    {
        return $this->basic('Contenu', static::content());
    }


    public function postContent($id = '')
    {

        $this->validate($this->request(), ['id' => 'exists:content,uuid']);



        // MODEL
        $voucher = new VoucherCode();

        // FORM
        $form = \form()->enableRemote();
        $form->setLegend('Campagne : ' . $voucher->value);

//        $form->addSelect('mission_sequence_sid', 'Parcours', $sequence);
//        $form->addSubmit('Enregistrer');
//
//         TRAITEMENT
//        if (request()->has('Enregistrer')) {
//            $form->valid(request()->all());
//            if ($form->isValid()) {
//                $data = $form->getFilteredValues();
//                try {
//                    $voucher->value = $data['value'];
//                    $voucher->partner_id = \user()->getPartnerId();
//                    $voucher->save();
//                    \js()->success()->closeRemoteModal()->reloadDataTable();
//                } catch (\Exception $e) {
//                    \js()->error($e->getMessage());
//                }
//            }
//        }

        return response()->modal($form);

//        dd('MIOCJV');
    }
}