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

    protected $denyReason;

    const DENY_REASON_BY_NON_TOKEN = 'non-token';
    const DENY_REASON_BY_TOKEN_NOT_MATCH = 'not-match';
    const DENY_REASON_BY_NOT_ALLOW = 'not-allow';

    public function getDenyReason()
    {
        return $this->denyReason;
    }

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
            $this->denyReason = self::DENY_REASON_BY_NON_TOKEN;
            return false;
        }

        if ($token->isSuperToken()) {
            return true;
        }

        $tokenStatus = $token->getTokenStatus();
        if (!$tokenStatus || empty($tokenStatus['roles'])) {
            $this->denyReason = self::DENY_REASON_BY_TOKEN_NOT_MATCH;
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
        $this->denyReason = self::DENY_REASON_BY_NOT_ALLOW;
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
