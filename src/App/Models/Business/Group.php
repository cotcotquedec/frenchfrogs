<?php

namespace FrenchFrogs\App\Models\Business;

use FrenchFrogs\App\Models\Db;
use FrenchFrogs\Business\Business;

/**
 * @deprecated
 *
 * Class Group
 */
class Group extends Business
{
    protected static $modelClass = Db\User\Group::class;
}
