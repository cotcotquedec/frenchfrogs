<?php namespace FrenchFrogs\Models\Business;

use BetterReflection\Reflection\Adapter\ReflectionClass;
use Carbon\Carbon;
use FrenchFrogs\Business\Business;

/**
 * Class Mail
 *
 * Gestion des email
 *
 * @package FrenchFrogs\Models\Business
 */
class Mail extends Business
{
    static protected $modelClass = \FrenchFrogs\Models\Db\Mail::class;

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
     * Envoie un email depuis la base
     *
     * @return $this
     */
    public function send()
    {
        /**@var  \FrenchFrogs\Models\Db\Mail $model */
        $model = $this->getModel();


        \Log::debug('BEGIN');

        // on met le pail en cours de traietemnt
        $model->mail_status_id = \Ref::MAIL_STATUS_PROCESSING;
        $model->processing_at = Carbon::now();
        $model->save();

        try {
            $class = new \ReflectionClass($model->class);
            $class = $class->newInstanceArgs(\json_decode($model->params));

            \Mail::send($class);

            $model->mail_status_id = \Ref::MAIL_STATUS_SENT;
            $model->sent_at = Carbon::now();
            \Log::debug('success');

        } catch (\Exception $e) {

            \Log::error('Erreur sur l\'envoie du mail ' . $e->getMessage());
            $model->mail_status_id = \Ref::MAIL_STATUS_ERROR;
            $model->error_at = Carbon::now();
            $model->message = $e->getMessage();
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
        $mail = \FrenchFrogs\Models\Db\Mail::where('mail_status_id', \Ref::MAIL_STATUS_CREATED)
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
}