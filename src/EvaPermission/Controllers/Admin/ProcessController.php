<?php

namespace Eva\EvaPermission\Controllers\Admin;

use Eva\EvaPermission\Auth\SessionAuthority;
use Eva\EvaPermission\Models;
use Eva\EvaPermission\Entities;
use Eva\EvaEngine\Mvc\Controller\JsonControllerInterface;
use Eva\EvaEngine\Exception;

/**
* @resourceName("Auth Managment Assists")
* @resourceDescription("Auth Assists (Ajax json format)")
*/
class ProcessController extends ControllerBase implements JsonControllerInterface
{

    /**
    * @operationName("Remove a role")
    * @operationDescription("Remove a role")
    */
    public function roleAction()
    {
        if (!$this->request->isDelete()) {
            return $this->showErrorMessageAsJson(405, 'ERR_REQUEST_METHOD_NOT_ALLOW');
        }

        $data = array(
            'roleId' => $this->dispatcher->getParam('id'),
            'operationId' => $this->dispatcher->getParam('subid'),
        );
        try {
            $roleOperation =  Entities\RolesOperations::findFirst(array(
                'conditions' => 'roleId = :roleId: AND operationId = :operationId:',
                'bind' => $data,
            ));
            if ($roleOperation) {
                $roleOperation->delete();
            }
        } catch (\Exception $e) {
            return $this->showExceptionAsJson($e, $roleOperation->getMessages());
        }

        return $this->response->setJsonContent($roleOperation);
    }

    /**
    * @operationName("Change API key")
    * @operationDescription("Change API key")
    */
    public function apikeyAction()
    {
        if (!$this->request->isPut()) {
            return $this->showErrorMessageAsJson(405, 'ERR_REQUEST_METHOD_NOT_ALLOW');
        }

        $id = $this->dispatcher->getParam('id');
        try {
            $apikey =  Entities\Apikeys::findFirst($id);
            if ($apikey) {
                $apikey->apikey = \Phalcon\Text::random(\Phalcon\Text::RANDOM_ALNUM, 8);
                $apikey->save();
            }
        } catch (\Exception $e) {
            return $this->showExceptionAsJson($e, $apikey->getMessages());
        }
        return $this->response->setJsonContent($apikey);
    }

    /**
    * @operationName("Remove user role relation")
    * @operationDescription("Remove user role relation")
    */
    public function userAction()
    {
        if (!$this->request->isDelete()) {
            return $this->showErrorMessageAsJson(405, 'ERR_REQUEST_METHOD_NOT_ALLOW');
        }

        $data = array(
            'userId' => $this->dispatcher->getParam('id'),
            'roleId' => $this->dispatcher->getParam('subid'),
        );
        try {
            $userRole =  Entities\UsersRoles::findFirst(array(
                'conditions' => 'userId = :userId: AND roleId = :roleId:',
                'bind' => $data,
            ));
            if ($userRole) {
                $userRole->delete();
            }
        } catch (\Exception $e) {
            return $this->showExceptionAsJson($e, $userRole->getMessages());
        }

        return $this->response->setJsonContent($userRole);
    }

    /**
    * @operationName("Apply role to select users")
    * @operationDescription("Apply role to select users")
    */
    public function applyRolesAction()
    {
        if (!$this->request->isPut()) {
            return $this->showErrorMessageAsJson(405, 'ERR_REQUEST_METHOD_NOT_ALLOW');
        }

        $idArray = $this->request->getPut('id');
        if (!is_array($idArray) || count($idArray) < 1) {
            return $this->showErrorMessageAsJson(401, 'ERR_REQUEST_PARAMS_INCORRECT');
        }

        $roleId = $this->request->getPut('roleId');
        $res = array();
        try {
            foreach ($idArray as $id) {
                $data = array(
                    'roleId' => $roleId,
                    'userId' => $id,
                );
                $userRole = Entities\UsersRoles::findFirst(array(
                    'conditions' => 'roleId = :roleId: AND userId = :userId:',
                    'bind' => $data,
                ));
                if (!$userRole) {
                    $userRole = new Entities\UsersRoles();
                    $userRole->assign($data);
                    $userRole->save();
                }
                $res[] = $userRole;
            }
        } catch (\Exception $e) {
            return $this->showExceptionAsJson($e, $userRole->getMessages());
        }

        return $this->response->setJsonContent($res);
    }


    /**
    * @operationName("Apply role to select operations")
    * @operationDescription("Apply role to select operations")
    */
    public function applyOperationsAction()
    {
        if (!$this->request->isPut()) {
            return $this->showErrorMessageAsJson(405, 'ERR_REQUEST_METHOD_NOT_ALLOW');
        }

        $idArray = $this->request->getPut('id');
        if (!is_array($idArray) || count($idArray) < 1) {
            return $this->showErrorMessageAsJson(401, 'ERR_REQUEST_PARAMS_INCORRECT');
        }

        $roleId = $this->request->getPut('roleid');
        $res = array();
        try {
            foreach ($idArray as $id) {
                $data = array(
                    'roleId' => $roleId,
                    'operationId' => $id,
                );
                $roleOperation =  Entities\RolesOperations::findFirst(array(
                    'conditions' => 'roleId = :roleId: AND operationId = :operationId:',
                    'bind' => $data,
                ));
                if (!$roleOperation) {
                    $roleOperation = new Entities\RolesOperations();
                    $roleOperation->assign($data);
                    $roleOperation->save();
                }
                $res[] = $roleOperation;
            }
        } catch (\Exception $e) {
            return $this->showExceptionAsJson($e, $roleOperation->getMessages());
        }

        return $this->response->setJsonContent($res);
    }
    /**
     * @operationName("Flush ACL cache")
     * @operationDescription("Flush ACL cache")
     */
    public function flushCacheAction()
    {
        $authority = new SessionAuthority();
        $authority->getAcl(true);
        $authority->setCache($this->getDI()->getGlobalCache());
        $this->flashSession->success('SUCCESS_FLUSH_ACL_CACHE');
        return $this->response->redirect('/admin/dashboard');
    }
}
