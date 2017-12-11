<?php namespace FrenchFrogs\App\Models\Business;

use FrenchFrogs\App\Models\Db;
use FrenchFrogs\Business\Business;

/**
 *
 *
 * @deprecated
 *
 * Class GroupUser
 * @package FrenchFrogs\App\Models\Business
 */
class GroupUser extends Business
{
    static protected $modelClass = Db\User\GroupUser::class;
}