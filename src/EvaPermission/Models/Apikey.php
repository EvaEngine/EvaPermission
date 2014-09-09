<?php

namespace Eva\EvaPermission\Models;

use Eva\EvaPermission\Entities;
use Eva\EvaEngine\Exception;
use Eva\EvaUser\Models\Login;

class Apikey extends Entities\Apikeys
{
    protected $token;

    protected $tokenStatus = false;

    public function beforeValidationOnCreate()
    {
        $this->createdAt = time();
        $this->apikey = \Phalcon\Text::random(\Phalcon\Text::RANDOM_ALNUM, 8);
    }

    public function setToken($token)
    {
        $this->token = $token;
        return $this;
    }

    public function getToken()
    {
        return $this->token;
    }

    public function isSuperToken()
    {
        if (!$this->token) {
            return false;
        }
        $config = $this->getDI()->getConfig()->permission->superkeys->toArray();
        if (in_array($this->token, $config)) {
            return true;
        }
        return false;
    }

    public function generateToken($userId)
    {
        $plan = $this->getDI()->getConfig()->permission->keyLevels->basic;
        $apikey = new Apikey();
        $apikey->userId = $userId;
        $apikey->level = 'basic';
        $apikey->minutelyRate = $plan->minutelyRate;
        $apikey->hourlyRate = $plan->hourlyRate;
        $apikey->dailyRate = $plan->dailyRate;
        if (!$apikey->save()) {
            throw new Exception\RuntimeException('ERR_PERMISSION_APIKEY_GENERATE_FAILED');
        }
        return $apikey;
    }

    public function getTokenStatus()
    {
        if ($this->tokenStatus !== false) {
            return $this->tokenStatus;
        }

        $token = $this->token;
        if (!$token) {
            return $this->tokenStatus = array();
        }
        $fastCache = $this->getDI()->getFastCache();
        $cacheKey = 'eva-permission-token-' . $token;
        if ($fastCache && $data = $fastCache->get($cacheKey)) {
            return $this->tokenStatus = json_decode($data, true);
        }

        $tokenObj = self::findFirst("apikey = '$token'");
        if ($tokenObj) {
            $token = array(
                'apikey' => $tokenObj->apikey,
                'userId' => $tokenObj->userId,
                'level' => $tokenObj->level,
                'minutelyRate' => $tokenObj->minutelyRate,
                'hourlyRate' => $tokenObj->hourlyRate,
                'dailyRate' => $tokenObj->dailyRate,
                'expiredAt' => $tokenObj->expiredAt,
            );
            $roles = array();
            if ($tokenObj->user && $userRoles = $tokenObj->user->roles) {
                foreach ($userRoles as $role) {
                    $roles[] = $role->roleKey;
                }
            }
            if ($tokenObj->user->status == 'active') {
                $roles[] = 'USER';
                $roles = array_unique($roles);
            }
            $token['roles'] = $roles;
        } else {
            $token = array();
        }

        $fastCache->set($cacheKey, json_encode($token));
        return $this->tokenStatus = $token;
    }

    public function isOutOfMinutelyRate()
    {
        $tokenStatus = $this->getTokenStatus();
        if (!$tokenStatus) {
            return true;
        }
        $fastCache = $this->getDI()->getFastCache();
        $cachePrefix = 'eva-permission-token-' . $tokenStatus['apikey'];
        $time = time();
        $cacheKey = $cachePrefix . '-' . ($time - $time % 60);
        $minutelyRate = $tokenStatus['minutelyRate'];

        $currentRate = $fastCache->get($cacheKey);
        if ($currentRate > $tokenStatus['minutelyRate']) {
            return true;
        }

        $fastCache->incr($cacheKey);
        return false;
    }

    public function isOutOfHourlyRate()
    {
        $tokenStatus = $this->getTokenStatus();
        if (!$tokenStatus) {
            return true;
        }
        $fastCache = $this->getDI()->getFastCache();
        $cachePrefix = 'eva-permission-token-' . $tokenStatus['apikey'];
        $time = time();
        $cacheKey = $cachePrefix . '-' . ($time - $time % 3600);
        $minutelyRate = $tokenStatus['hourlyRate'];

        if ($currentRate = $fastCache->get($cacheKey) && $currentRate > $tokenStatus['hourlyRate']) {
            return true;
        }

        $fastCache->incr($cacheKey);
        return false;
    }

    public function isOutOfDailyRate()
    {
        $tokenStatus = $this->getTokenStatus();
        if (!$tokenStatus) {
            return true;
        }
        $fastCache = $this->getDI()->getFastCache();
        $cachePrefix = 'eva-permission-token-' . $tokenStatus['apikey'];
        $time = time();
        $cacheKey = $cachePrefix . '-' . ($time - $time % 86400);
        $minutelyRate = $tokenStatus['dailyRate'];

        if ($currentRate = $fastCache->get($cacheKey) && $currentRate > $tokenStatus['dailyRate']) {
            return true;
        }

        $fastCache->incr($cacheKey);
        return false;
    }

    public function loginByApikey($apikey)
    {
        Login::setLoginMode(Login::LOGIN_MODE_TOKEN);
        $token = Apikey::findFirst(array(
            'conditions' => 'apikey = :apikey:',
            'bind' => array(
                'apikey' => $apikey,
            )
        ));
        if (!$token) {
            throw new Exception\UnauthorizedException('ERR_PERMISSION_APIKEY_NOT_EXIST');
        }
        $userId = $token->userId;
        $login = new Login();
        $login->id = $userId;
        $login->login();
    }
}
