<?php

namespace Eva\EvaPermission\Events;

use Eva\EvaEngine\Exception;
use Eva\EvaEngine\Mvc\Controller\SessionAuthorityControllerInterface;
use Eva\EvaEngine\Mvc\Controller\TokenAuthorityControllerInterface;
use Eva\EvaEngine\Mvc\Controller\RateLimitControllerInterface;
use Eva\EvaPermission\Entities;
use Eva\EvaPermission\Auth;

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
                $dispatcher->setModuleName('EvaPermission');
                $dispatcher->setNamespaceName('Eva\EvaPermission\Controllers');
                $dispatcher->setControllerName('Error');
                $dispatcher->setActionName('index');
                $dispatcher->forward(array(
                    "controller" => "error",
                    "action" => "index",
                    "params" => array(
                        'activeController' => $controller,
                    )
                ));
                $dispatcher->getDI()->getResponse()->setHeader('X-Permission-Auth', 'Deny-By-Session');
                return false;
            }
            $dispatcher->getDI()->getResponse()->setHeader('X-Permission-Auth', 'Allow-By-Session');
        } elseif ($controller instanceof TokenAuthorityControllerInterface) {
            $auth = new Auth\TokenAuthority();
            $auth->setApikey($dispatcher->getDI()->getRequest()->get('api_key'));
            $auth->setCache($dispatcher->getDI()->getGlobalCache());
            //$auth->setFastCache($dispatcher->getDI()->getFastCache());
            if (!$auth->checkAuth(get_class($controller), $dispatcher->getActionName())) {
                $dispatcher->getDI()->getResponse()->setHeader('X-Permission-Auth', 'Deny-By-Token');
                throw new Exception\UnauthorizedException('Permission not allowed');
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
