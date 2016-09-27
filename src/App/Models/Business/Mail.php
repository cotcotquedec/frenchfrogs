<?php namespace FrenchFrogs\App\Models\Business;

use Carbon\Carbon;
use FrenchFrogs\Business\Business;
use FrenchFrogs\Laravel\Mail\Mailable;

/**
 * Class Mail
 *
 * Gestion des email
 *
 * @package FrenchFrogs\App\Models\Business
 */
class Mail extends Business
{
    static protected $modelClass = \FrenchFrogs\App\Models\Db\Mail::class;

    /**
     * Création d'un mail dans la base
     *
     * @param $class
     * @param null $params
     * @return Business
     */
    static public function push($class, ...$params)
    {
        return static::create([
            'class' => $class,
            'params' => is_null($params) ? null : \json_encode($params),
            'mail_status_id' => \Ref::MAIL_STATUS_CREATED
        ]);
    }

    /**
     * Track email
     *
     * @param array $query
     * @return $this
     */
    public function track($query = [])
    {
        $model = $this->getModel();

        // on met le mail en cours de traitement
        if ($model->mail_status_id != \Ref::MAIL_STATUS_OPENED) {
            $model->mail_status_id = \Ref::MAIL_STATUS_OPENED;
            $model->opened_at = Carbon::now();
            $model->save();
        }

        return $this;
    }

    /**
     * Envoie un email depuis la base
     *
     * @return $this
     */
    public function send()
    {
        /**@var  \FrenchFrogs\App\Models\Db\Mail $model */
        $model = $this->getModel();

        // on met le pail en cours de traietemnt
        $model->mail_status_id = \Ref::MAIL_STATUS_PROCESSING;
        $model->processing_at = Carbon::now();
        $model->save();

        try {

            $mail = $this->build();

            // envoie du mail
            \Mail::send($mail);

            $model->mail_status_id = \Ref::MAIL_STATUS_SENT;
            $model->sent_at = Carbon::now();

            ld('Email OK : ' . $model->class . ' ' . $model->params);
        } catch (\Exception $e) {
            $model->mail_status_id = \Ref::MAIL_STATUS_ERROR;
            $model->error_at = Carbon::now();
            $model->message = $e->getMessage();

            le('Email ERROR : ' . $e->getMessage() .  ' : ' . $model->class . ' ' . $model->params);
        }

        $model->save();

        return $this;
    }


    /**
     * Envoie le prochain email
     *
     * @return bool
     */
    static public function next()
    {
        // Selection de l'email a envoyer
        $mail = \FrenchFrogs\App\Models\Db\Mail::where('mail_status_id', \Ref::MAIL_STATUS_CREATED)
            ->orderBy('created_at')
            ->first();

        if (empty($mail)) {
            return false;
        }

        // Envoie du mail
        $class = static::get($mail->mail_uuid)->send();

        // renvoie si le mail a été envoyé
        return $class->getModel()->isSent();
    }

    /**
     *
     * @return Mailable
     */
    public function build()
    {
        /**@var  \FrenchFrogs\App\Models\Db\Mail $model */
        $model = $this->getModel();
        $class = new \ReflectionClass($model->class);

        // CReation de l'instance
        $mail = $class->newInstanceArgs(\json_decode($model->params));
        $mail->setUuid($model->getKey());

        // parametreage du mail
        $mail->build();

        return $mail;
    }
}