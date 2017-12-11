<?php namespace FrenchFrogs\App\Models\Business;

use FrenchFrogs\App\Models\Db;
use FrenchFrogs\Business\Business;

/**
 * @deprecated
 *
 * Class Group
 * @package FrenchFrogs\App\Models\Business
 */
class Group extends Business
{
    static protected $modelClass = Db\User\Group::class;
}