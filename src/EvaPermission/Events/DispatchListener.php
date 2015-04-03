<?php

namespace Eva\EvaPermission\Events;

use Eva\EvaEngine\Exception;
use Eva\EvaEngine\Mvc\Controller\SessionAuthorityControllerInterface;
use Eva\EvaEngine\Mvc\Controller\TokenAuthorityControllerInterface;
use Eva\EvaEngine\Mvc\Controller\RateLimitControllerInterface;
use Eva\EvaPermission\Entities;
use Eva\EvaPermission\Auth;
use Eva\EvaEngine\Service\TokenStorage;

class DispatchListener
{
    public function beforeExecuteRoute($event)
    {
        $dispatcher = $event->getSource();
        if ($dispatcher->getDI()->getConfig()->permission->disableAll) {
            $dispatcher->getDI()->getResponse()->setHeader('X-Permission-Auth', 'Allow-By-Disabled-Auth');
            return true;
        }
        $controller = $dispatcher->getActiveController();

        //Not need to authority
        if ($controller instanceof SessionAuthorityControllerInterface) {
            $auth = new Auth\SessionAuthority();
            $auth->setCache($dispatcher->getDI()->getGlobalCache());
            if (!$auth->checkAuth(get_class($controller), $dispatcher->getActionName())) {
                try {
                    $errorHandlerConfig = $dispatcher->getDI()->get('evaPermissionErrorHandlerConfig');
                } catch (\Exception $e) {
                    $errorHandlerConfig = [];
                }
                if (!$errorHandlerConfig) {
                    $errorHandlerConfig = [
                        'module' => 'EvaPermission',
                        'namespace' => 'Eva\EvaPermission\Controllers',
                        'forward' => array(
                            "controller" => "error",
                            "action" => "index",
                        )
                    ];
                }

                $errorHandlerConfig['forward']['params'] = array(
                    'activeController' => $controller,
                );
                $dispatcher->setModuleName($errorHandlerConfig['module']);
                $dispatcher->setNamespaceName($errorHandlerConfig['namespace']);
                $dispatcher->forward($errorHandlerConfig['forward']);
//                $dispatcher->setModuleName('EvaPermission');
//                $dispatcher->setNamespaceName('Eva\EvaPermission\Controllers');
//                $dispatcher->setControllerName('Error');
//                $dispatcher->setActionName('index');
//                $dispatcher->forward(array(
//                    "controller" => "error",
//                    "action" => "index",
//                    "params" => array(
//                        'activeController' => $controller,
//                    )
//                ));
                $dispatcher->getDI()->getResponse()->setHeader('X-Permission-Auth', 'Deny-By-Session');
                return false;
            }
            $dispatcher->getDI()->getResponse()->setHeader('X-Permission-Auth', 'Allow-By-Session');
        } elseif ($controller instanceof TokenAuthorityControllerInterface) {
            $auth = new Auth\TokenAuthority();
            $apikey = TokenStorage::dicoverToken($dispatcher->getDI()->getRequest());
            if (!$apikey) {
                throw new Exception\UnauthorizedException('ERR_AUTH_TOKEN_NOT_INPUT');
            }
            $auth->setApikey($apikey);
            $auth->setCache($dispatcher->getDI()->getGlobalCache());
            if (!$auth->checkAuth(get_class($controller), $dispatcher->getActionName())) {
                $dispatcher->getDI()->getResponse()->setHeader('X-Permission-Auth', 'Deny-By-Token');
                $denyReason = $auth->getDenyReason();
                switch ($denyReason) {
                    case Auth\TokenAuthority::DENY_REASON_BY_NON_TOKEN:
                        throw new Exception\UnauthorizedException('ERR_AUTH_TOKEN_NOT_INPUT');
                    case Auth\TokenAuthority::DENY_REASON_BY_TOKEN_NOT_MATCH:
                        throw new Exception\UnauthorizedException('ERR_AUTH_TOKEN_NOT_MATCH');
                    default:
                        throw new Exception\UnauthorizedException('ERR_AUTH_PERMISSION_NOT_ALLOW');
                }
            }
            if ($controller instanceof RateLimitControllerInterface && !$auth->checkLimitRate()) {
                $dispatcher->getDI()->getResponse()->setHeader('X-Permission-Auth', 'Deny-By-Token');
                throw new Exception\OperationNotPermitedException('Operation out of limit rate');
            }
            $dispatcher->getDI()->getResponse()->setHeader('X-Permission-Auth', 'Allow-By-Token');
            return true;
        } else {
            $dispatcher->getDI()->getResponse()->setHeader('X-Permission-Auth', 'Allow-By-Public-Resource');
            return true;
        }
    }
}
