<?php

namespace FrenchFrogs\App\Models\Db;

use FrenchFrogs\Laravel\Database\Eloquent\Model;

class Mail extends Model
{
    protected $primaryKey = 'mail_uuid';
    public $primaryUuid = true;
    protected $table = 'mail';

    /**
     * Return true if mail is sent.
     *
     * @return bool
     */
    public function isSent()
    {
        return $this->exists && !empty($this->sent_at);
    }
}
