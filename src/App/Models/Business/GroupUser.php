<?php namespace FrenchFrogs\Models\Business;

use FrenchFrogs\Models\Db;
use FrenchFrogs\Business\Business;

/**
 *
 *
 * @deprecated
 *
 * Class GroupUser
 * @package FrenchFrogs\Models\Business
 */
class GroupUser extends Business
{
    static protected $modelClass = Db\User\GroupUser::class;
}