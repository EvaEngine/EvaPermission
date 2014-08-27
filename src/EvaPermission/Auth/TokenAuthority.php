<?php

namespace Eva\EvaPermission\Auth;

use Phalcon\Acl;
use Eva\EvaEngine\Exception;
use Eva\EvaPermission\Entities;
use Eva\EvaPermission\Models\Apikey;
use Phalcon\Cache\Backend as BackendCache;

class TokenAuthority extends AbstractAuthority
{
    protected $apikey;

    protected $token;

    public function setApikey($apikey)
    {
        $this->apikey = $apikey;
        return $this;
    }

    public function getApikey()
    {
        return $this->apikey;
    }

    public function getToken()
    {
        if ($this->token) {
            return $this->token;
        }
        $token = new Apikey();
        $token->setToken($this->apikey);
        return $this->token = $token;
    }

    public function checkAuth($resource, $operation)
    {
        $token = $this->getToken();
        if (!$token) {
            return false;
        }

        if ($token->isSuperToken()) {
            return true;
        }
        $tokenStatus = $token->getTokenStatus();
        if (empty($tokenStatus['roles'])) {
            return false;
        }

        $roles = $tokenStatus['roles'];
        $acl = $this->getAcl();
        foreach ($roles as $role) {
            //If any of roles allowed permission
            if ($acl->isAllowed($role, $resource, $operation)) {
                return true;
            }
        }
        return false;
    }

    public function checkLimitRate()
    {
        $token = $this->getToken();
        if ($token->isOutOfMinutelyRate() + $token->isOutOfHourlyRate() + $token->isOutOfDailyRate() > 0) {
            return false;
        }
        return true;
    }
}
