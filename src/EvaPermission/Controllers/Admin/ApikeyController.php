<?php

namespace Eva\EvaPermission\Controllers\Admin;

use Eva\EvaPermission\Entities;
use Eva\EvaPermission\Models;
use Eva\EvaUser\Entities\Users;
use Eva\EvaPermission\Forms;
use Eva\EvaEngine\Exception;

/**
* @resourceName("Auth API key Managment")
* @resourceDescription("Auth API key Managment")
*/
class ApikeyController extends ControllerBase
{
    /**
    * @operationName("API key list")
    * @operationDescription("API key list")
    */
    public function indexAction()
    {
        $query = array(
            'limit' => 1000,
            'page' => $this->request->getQuery('page', 'int', 1),
        );
        $itemQuery = $this->getDI()->getModelsManager()->createBuilder()
        ->from('Eva\EvaPermission\Entities\Apikeys');

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
    * @operationName("Create API key")
    * @operationDescription("Create API key")
    */
    public function createAction()
    {
        $form = new Forms\ApikeyForm();
        $apikey = new Models\Apikey();
        $form->setModel($apikey);
        $this->view->setVar('form', $form);
        if ($uid = $this->request->get('uid')) {
            if (Models\Apikey::findFirst("userId = $uid")) {
                return $this->response->redirect('/admin/permission/apikey/edit/' . $uid);
            }
            $this->view->setVar('user', Users::findFirst($uid));
        }

        if (!$this->request->isPost()) {
            return false;
        }

        $form->bind($this->request->getPost(), $apikey);
        if (!$form->isValid()) {
            return $this->showInvalidMessages($form);
        }
        $apikey = $form->getEntity();
        try {
            if (!$apikey->save()) {
                return $this->showModelMessages($apikey);
            }
        } catch (\Exception $e) {
            return $this->showException($e, $apikey->getMessages());
        }
        $this->flashSession->success('SUCCESS_APIKEY_CREATED');

        return $this->redirectHandler('/admin/permission/apikey/edit/' . $apikey->id);
    }

    /**
    * @operationName("Edit API key")
    * @operationDescription("Edit API key")
    */
    public function editAction()
    {
        $this->view->changeRender('admin/apikey/create');

        $form = new Forms\ApikeyForm();
        $apikey = Models\Apikey::findFirst($this->dispatcher->getParam('id'));
        $form->setModel($apikey ? $apikey : new Models\Apikey());
        $this->view->setVar('form', $form);
        $this->view->setVar('item', $apikey);
        if (!$this->request->isPost()) {
            return false;
        }

        $form->bind($this->request->getPost(), $apikey);
        if (!$form->isValid()) {
            return $this->showInvalidMessages($form);
        }
        $apikey = $form->getEntity();
        $apikey->assign($this->request->getPost());
        try {
            $apikey->save();
        } catch (\Exception $e) {
            return $this->showException($e, $apikey->getMessages());
        }
        $this->flashSession->success('SUCCESS_APIKEY_UPDATED');

        return $this->redirectHandler('/admin/permission/apikey/edit/' . $apikey->id);
    }

    /**
    * @operationName("Remove API key")
    * @operationDescription("Remove API key")
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
        $apikey = Models\Apikey::findFirst($id);
        try {
            $apikey->delete();
        } catch (\Exception $e) {
            return $this->showExceptionAsJson($e, $apikey->getMessages());
        }
        return $this->response->setJsonContent($apikey);
    }
}
