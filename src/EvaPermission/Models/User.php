<?php

namespace Eva\EvaPermission\Models;

use Eva\EvaUser\Models\Login;
use Eva\EvaEngine\Exception;

class User extends Login
{
    public function isSuperUser()
    {
        $user = Login::getCurrentUser();
        if (!$user['id']) {
            return false;
        }
        $superUsers = $this->getDI()->getConfig()->permission->superusers->toArray();
        return in_array($user['id'], $superUsers) ? true : false;
    }

    public function getRoles()
    {
        $user = Login::getCurrentUser();
        if (!$user['id']) {
            return array('GUEST');
        }
        $storage = Login::getAuthStorage();
        $authRoles = $storage->get(Login::AUTH_KEY_ROLES);
        $authRoles = $authRoles ?: array();
        //Add default roles
        if ($user['status'] == 'active') {
            $authRoles[] = 'USER';
            $authRoles = array_unique($authRoles);
        }
        return $authRoles;
    }
}
