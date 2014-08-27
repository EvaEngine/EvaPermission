<?php

namespace Eva\EvaPermission\Controllers\Admin;

use Eva\EvaPermission\Entities;
use Eva\EvaPermission\Forms;
use Eva\EvaEngine\Exception;

/**
* @resourceName("Auth Role Managment")
* @resourceDescription("Auth Role Managment")
*/
class RoleController extends ControllerBase
{
    /**
    * @operationName("Role List")
    * @operationDescription("Get role list")
    */
    public function indexAction()
    {
        $query = array(
            'limit' => 1000,
            'page' => $this->request->getQuery('page', 'int', 1),
        );
        $itemQuery = $this->getDI()->getModelsManager()->createBuilder()
            ->from('Eva\EvaPermission\Entities\Roles');

        $paginator = new \Eva\EvaEngine\Paginator(array(
            "builder" => $itemQuery,
            "limit"=> $query['limit'],
            "page" => $query['page']
        ));
        $paginator->setQuery($query);
        $pager = $paginator->getPaginate();
        $this->view->setVar('pager', $pager);
    }

    /**
    * @operationName("Create Role")
    * @operationDescription("Create Role")
    */
    public function createAction()
    {
        $form = new Forms\RoleForm();
        $role = new Entities\Roles();
        $form->setModel($role);
        $this->view->setVar('form', $form);

        if (!$this->request->isPost()) {
            return false;
        }

        $form->bind($this->request->getPost(), $role);
        if (!$form->isValid()) {
            return $this->showInvalidMessages($form);
        }
        $role = $form->getEntity();
        try {
            if (!$role->save()) {
                return $this->showModelMessages($role);
            }
        } catch (\Exception $e) {
            return $this->showException($e, $role->getMessages());
        }
        $this->flashSession->success('SUCCESS_ROLE_CREATED');

        return $this->redirectHandler('/admin/permission/role/edit/' . $role->id);
    }

    /**
    * @operationName("Edit Role")
    * @operationDescription("Edit Role")
    */
    public function editAction()
    {
        $this->view->changeRender('admin/role/create');

        $form = new Forms\RoleForm();
        $role = Entities\Roles::findFirst($this->dispatcher->getParam('id'));
        $form->setModel($role ? $role : new Entities\Roles());
        $this->view->setVar('form', $form);
        $this->view->setVar('item', $role);
        if (!$this->request->isPost()) {
            return false;
        }

        $form->bind($this->request->getPost(), $role);
        if (!$form->isValid()) {
            return $this->showInvalidMessages($form);
        }
        $role = $form->getEntity();
        $role->assign($this->request->getPost());
        try {
            $role->save();
        } catch (\Exception $e) {
            return $this->showException($e, $role->getMessages());
        }
        $this->flashSession->success('SUCCESS_ROLE_UPDATED');

        return $this->redirectHandler('/admin/permission/role/edit/' . $role->id);
    }

    /**
    * @operationName("Remove Role")
    * @operationDescription("Remove Role")
    */
    public function deleteAction()
    {
        $this->response->setContentType('application/json', 'utf-8');
        if (!$this->request->isDelete()) {
            $this->response->setStatusCode('405', 'Method Not Allowed');
            return $this->response->setJsonContent(array(
                'errors' => array(
                    array(
                        'code' => 405,
                        'message' => 'ERR_POST_REQUEST_METHOD_NOT_ALLOW'
                    )
                ),
            ));
        }

        $id = $this->dispatcher->getParam('id');
        $role = Entities\Roles::findFirst($id);
        try {
            $role->delete();
        } catch (\Exception $e) {
            return $this->showExceptionAsJson($e, $role->getMessages());
        }
        return $this->response->setJsonContent($role);
    }
}
