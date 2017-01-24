<?php namespace FrenchFrogs\App\Models\Db;


use \Carbon\Carbon;
use Illuminate\Database\Eloquent\SoftDeletes;


/**
 * 
 *
 * @property $uuid
 * @property $content_index
 * @property $lang_sid
 * @property $content
 * @property $is_published
 * @property Carbon $published_at
 * @property Carbon $deleted_at
 * @property Carbon $created_at
 * @property Carbon $updated_at
 */
class Content extends \FrenchFrogs\Laravel\Database\Eloquent\Model
{
	use SoftDeletes;

	/**
	 * 
	 *
	 */
	protected $table = 'content';
	
	
	/**
	 * 
	 *
	 */
	protected $primaryKey = 'uuid';


    public $primaryUuid = true;
	
	
	/**
	 * 
	 *
	 */
	protected $dates = [
	    "published_at",
	    "deleted_at",
	    "created_at",
	    "updated_at"
	];
}