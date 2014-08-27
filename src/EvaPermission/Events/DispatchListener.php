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
                return false;
            }
        } elseif ($controller instanceof TokenAuthorityControllerInterface) {
            $auth = new Auth\TokenAuthority();
            $auth->setApikey($dispatcher->getDI()->getRequest()->get('api_key'));
            $auth->setCache($dispatcher->getDI()->getGlobalCache());
            //$auth->setFastCache($dispatcher->getDI()->getFastCache());
            if (!$auth->checkAuth(get_class($controller), $dispatcher->getActionName())) {
                throw new Exception\UnauthorizedException('Permission not allowed');
            }
            if ($controller instanceof RateLimitControllerInterface && !$auth->checkLimitRate()) {
                throw new Exception\OperationNotPermitedException('Operation out of limit rate');
            }
            return true;
        } else {
            return true;
        }
    }
}
