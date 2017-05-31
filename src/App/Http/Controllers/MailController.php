<?php namespace FrenchFrogs\App\Http\Controllers;


use App\Http\Controllers\Controller;
use FrenchFrogs\App\Models\Business\Mail;
use Illuminate\Database\Query\JoinClause;
use Illuminate\Http\Request;
use App\Http\Requests;
use Models\Acl\Inside as Acl;


/**
 *
 *
 */
class MailController extends Controller
{
    /**
     * Liste des candidats
     *
     *
     */
    public static function mail()
    {

        // QUERY
        $query = \query('mail as m', [
            raw('HEX(mail_uuid) as  uuid'),
            'class',
            'params',
            'r.name',
            'm.created_at',
            'm.sent_at',
            raw('message IS NOT NULL as error')
        ])
            ->join('reference as r', function (JoinClause $join) {
                $join->on('m.mail_status_id', 'reference_id')->where('collection', 'mail_status');
            });

        $table = \table($query);
        $table->setConstructor(static::class, __FUNCTION__)->enableRemote()->enableDatatable();
        $table->useDefaultPanel('Queue de mails');

        // COLMUMN
        $table->addText('name', 'Statut')->setStrainerSelect(\ref('mail_status')->pairs());
        $table->addDatetime('created_at', 'Crée le')->setStrainerDateRange('pa.created_at')->setOrder('created_at');
        $table->addDatetime('sent_at', 'Envoyé le')->setStrainerDateRange('sent_at')->setOrder('sent_at');
        $table->addText('class', 'Mail')->setStrainerText('class');
        $table->addText('params', 'Paramètres')->setStrainerText('params')->setWidth(100);
        $table->addBoolean('error', 'Erreur');

        if (!request()->isXmlHttpRequest()) {
            $table->getColumn('created_at')->order('desc');
        }

        // ACTION
        $action = $table->addContainer('action', 'Action')->setWidth(160);
        $action
            ->addButton('send', 'Envoyer', action_url(static::class, 'postSend', '%s'), 'uuid')
            ->enableCallback('post')
            ->icon('fa fa-paper-plane')
            ->setOptionAsPrimary();


        return $table;
    }

    /**
     * Gestion des email
     *
     */
    function getIndex()
    {
        return $this->basic('Mail', static::mail());
    }

    /**
     * Envoie un email
     *
     * @param $uuid
     * @return \FrenchFrogs\Container\Javascript
     */
    public function postSend($uuid)
    {
        //RULER
        \ruler()->check(
            null,
            ['id' => 'exists:mail,mail_uuid'],
            ['id' => $uuid = f($uuid, 'uuid')]
        );

        try {

            Mail::get($uuid)->send();
            \js()->success()->closeRemoteModal()->reloadDataTable();
        } catch (\Exception $e) {
            \js()->error($e->getMessage());
        }

        return \js();
    }


    /**
     * Peview an email
     *
     * @param $uuid
     * @return string
     */
    public function postPreview($hexid)
    {
        //RULER
        \ruler()->check(
            null,
            ['id' => 'exists:mail,mail_uuid'],
            ['id' => $uuid = f($hexid, 'uuid')]
        );

        // MODEL
        $business = Mail::get($uuid);
        $mail = $business->build();

        // FORM
        $form = \form();
        $form->setLegend('Prévisualisation mail');
        $form->addLabel('class', 'Modèle')->setValue($business->getModel()->class);
        $form->addLabel('params', 'Paramètres')->setValue($business->getModel()->params);
        $form->addLabel('subject', 'Sujet')->setValue($mail->previewSubject());
        $form->addLabel('from', 'Expediteur')->setValue($mail->previewFrom());
        $form->addLabel('to', 'Destinataire')->setValue($mail->previewTo());
        $form->addTitle('Contenu HTML');
        $form->addContent('contenu', html('iframe', [
            'width' => '100%',
            'height' => 800,
            'style' => 'border:none',
            'src' => action_url(static::class, 'getRender', $hexid)
        ]));

        $form->addTitle('Contenu texte');
        $form->addContent('contenu.text', html('pre', [], $mail->previewPlain()));


        return \response()->modal($form);
    }

    /**
     * Rendu de l'ifrma e html du mail
     *
     * @return string
     */
    public function getRender($hexid)
    {

        //RULER
        \ruler()->check(
            null,
            ['id' => 'exists:mail,mail_uuid'],
            ['id' => $uuid = f($hexid, 'uuid')]
        );

        $mail = Mail::get($uuid)->build();

        return $mail->previewHtml();
    }
}