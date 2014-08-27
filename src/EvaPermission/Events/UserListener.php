<?php

namespace Eva\EvaPermission\Events;

use Eva\EvaEngine\Exception;
use Eva\EvaEngine\Mvc\Controller\SessionAuthorityControllerInterface;
use Eva\EvaPermission\Entities;
use Eva\EvaPermission\Auth;
use Eva\EvaPermission\Models\User;
use Eva\EvaPermission\Models\Apikey;
use Eva\EvaUser\Models\Login;

class UserListener
{
    public function afterLogin($event, $loginUser)
    {
        if (!$loginUser->id) {
            return;
        }

        $storage = Login::getAuthStorage();
        if (Login::getLoginMode() == Login::LOGIN_MODE_TOKEN) {
            $apikey = new Apikey();
            $userId = $loginUser->id;
            $token = $apikey->findFirst("userId = $userId");
            if (!$token) {
                $token = $apikey->generateToken($userId);
            }
            $storage->setId($token->apikey);
            $storage->set(Login::AUTH_KEY_TOKEN, $token);
        }

        $defaultRoles = $loginUser->getRoles();
        $roles = $loginUser->roles;
        $authRoles = array();
        if ($roles) {
            foreach ($roles as $role) {
                $authRoles[] = $role->roleKey;
            }
        }
        $authRoles = array_unique(array_merge($defaultRoles, $authRoles));
        $storage->set(Login::AUTH_KEY_ROLES, $authRoles);
    }
}
