<?php namespace FrenchFrogs\App\Http\Controllers;


use App\Http\Controllers\Controller;
use Carbon\Carbon;
use FrenchFrogs\App\Models\Business\Mail;
use FrenchFrogs\App\Models\Db\Content;
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
//
//
//        // MODEL
//        $voucher = new VoucherCode();
//
//        // FORM
//        $form = \form()->enableRemote();
//        $form->setLegend('Campagne : ' . $voucher->value);

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
    }


    /**
     * Edition
     *
     * @param $id
     */
    public function edit($id)
    {
        // validation
        $this->validate($request = $this->request(),  ['id' => 'exists:content,content_index']);

        //MODEL
        $content = Content::where('content_index', $id)->orderBy('created_at', 'desc')->first();

        // FORM
        $form = \form()->enableRemote();
        $form->setLegend('Contenu : ' . $id);
        $form->addTextarea('content', 'Contenu')->addAttribute('rows', 10)->addStyle('resize', 'none');
        $form->addSubmit('Enregistrer');

        if (!$content->is_published) {
            $form->addSubmit('Supprimer')->setOptionAsDanger();
        }

        //TRAITEMENT
        if ($request->has('Enregistrer')) {
            $form->valid($request->all());
            if ($form->isValid()) {
                $data = $form->getFilteredValues();
                try {

                    if ($content->is_published) {
                        $content = Content::create([
                            'is_published' => false,
                            'content_index' => $content->content_index,
                            'content' => $content->content,
                            'lang_sid' => $content->lang_sid,
                        ]);
                    }

                    $content->content = $data['content'];
                    $content->save();
                    \js()->success()->closeRemoteModal()->appendJs('[data-edit-id=' . $id . ']', 'html', $content->content);
                } catch (\Exception $e) {
                    \js()->error($e->getMessage());
                }
            }

        } elseif($request->has('Supprimer')){
             try {
                 // on force le delete
                 !$content->is_published && $content->forceDelete();

                 //On recherche l'ancien model
                 $content = Content::where('content_index', $id)->orderBy('created_at', 'desc')->first();
                 \js()->success()->closeRemoteModal()->appendJs('[data-edit-id=' . $id . ']', 'html', $content->content);
             } catch (\Exception $e) {
                 \js()->error($e->getMessage());
             }

        } else {
            $form->populate($content->toArray());
        }

        return response()->modal($form);
    }


    /**
     * Activation
     *
     * @return $this
     */
    public function activate()
    {
        \Session::put('ff-edit', true);
        return \js()->reload();
    }

    /**
     * Activation
     *
     * @return $this
     */
    public function desactivate()
    {
        \Session::forget('ff-edit');
        return \js()->reload();
    }

    /**
     *
     * Publication
     *
     *
     */
    public function publish()
    {

        // recuperation de l'id
        $ids = $this->request()->get('id');

        // recuperation des ID
        $ids = explode(',', $ids);

        // flag de publication
        $published = false;

        // Traitement
        foreach ($ids as $id) {

            // si pas d'id
            if (empty($id)) {
                continue;
            }

            // recuperation du contenu
            $content = Content::where('content_index', $id)->orderBy('created_at', 'desc')->first();

            // Publication
            if ($content && !$content->is_published) {
                Content::where('content_index', $id)->where('is_published', true)->update(['is_published' => false]);
                $content->update(['is_published' => true, 'published_at' => Carbon::now()]);
                $published = true;
            }
        }

        // si il y a eu une publication, je reconstruit le contenu
        $published && \Artisan::call('content:build');

        return  \js()->success()->reload();
    }
}