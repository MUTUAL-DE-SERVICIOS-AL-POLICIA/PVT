<?php

namespace App\Observers;

use App\Role;
use App\Permission;
use App\Helpers\Util;

class RoleObserver
{
    public function pivotAttached(Role $object, $relationName, $pivotIds, $pivotIdsAttributes)
    {
        $object->role_id = 1;
        Util::save_record($object, 'sistema', Util::pivot_action($relationName, $pivotIds, 'agregó'));
    }

    public function pivotDetached(Role $object, $relationName, $pivotIds)
    {
        $object->role_id = 1;
        Util::save_record($object, 'sistema', Util::pivot_action($relationName, $pivotIds, 'el  iminó'));
    }
}