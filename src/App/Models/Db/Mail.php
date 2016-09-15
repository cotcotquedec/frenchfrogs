<?php namespace FrenchFrogs\Models\Db;

use FrenchFrogs\Laravel\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Mail extends Model
{
    protected $primaryKey = 'mail_uuid';
    public $primaryUuid = true;
    protected $table = 'mail';

    /**
     * Return true if mail is sent
     *
     * @return bool
     */
    public function isSent()
    {
        return $this->exists && !empty($this->sent_at);
    }
}