<?php

namespace Eva\EvaPermission\Auth;

use Phalcon\Acl\Adapter\Memory as MemoryAcl;
use Phalcon\Acl;
use Eva\EvaEngine\Exception;
use Eva\EvaPermission\Entities;
use Eva\EvaPermission\Models\User as LoginUser;
use Phalcon\Cache\Backend as BackendCache;

class SessionAuthority extends AbstractAuthority
{
    public function setUser(LoginUser $user)
    {
        $this->user = $user;
        return $this;
    }

    public function getUser()
    {
        if (!$this->user) {
            return $this->user = new LoginUser();
        }
        return $this->user;
    }

    public function checkAuth($resource, $operation)
    {
        $user = $this->getUser();
        if (!$user->isUserLoggedIn()) {
            return false;
        }

        if ($user->isSuperUser()) {
            return true;
        }

        $roles = $user->getRoles();
        $acl = $this->getAcl();
        foreach ($roles as $role) {
            //If any of roles allowed permission
            if ($acl->isAllowed($role, $resource, $operation)) {
                return true;
            }
        }
        return false;
    }
}
