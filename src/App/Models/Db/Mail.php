<?php namespace FrenchFrogs\App\Models\Db;

use FrenchFrogs\Laravel\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Class Mail
 *
 *
 * @package FrenchFrogs\App\Models\Db
 */
class Mail extends Model
{
    protected $primaryKey = 'mail_uuid';
    public $keyType = Model::BINARY16_UUID;
    protected $table = 'mail';

    protected $casts = [
        'data' => 'json',
    ];

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