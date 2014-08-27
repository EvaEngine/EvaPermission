<?php

namespace Eva\EvaPermission\Controllers\Admin;

use Eva\EvaPermission\Entities;
use Eva\EvaPermission\Forms;
use Eva\EvaEngine\Exception;

/**
* @resourceName("Auth Resource Managment")
* @resourceDescription("Auth Resource Managment")
*/
class ResourceController extends ControllerBase
{
    /**
    * @operationName("Resource List")
    * @operationDescription("Get resource list")
    */
    public function indexAction()
    {
        $query = array(
            'limit' => 1000,
            'page' => $this->request->getQuery('page', 'int', 1),
        );
        $itemQuery = $this->getDI()->getModelsManager()->createBuilder()
            ->from('Eva\EvaPermission\Entities\Resources');

        $paginator = new \Eva\EvaEngine\Paginator(array(
            "builder" => $itemQuery,
            "limit"=> $query['limit'],
            "page" => $query['page']
        ));
        $paginator->setQuery($query);
        $pager = $paginator->getPaginate();
        $this->view->setVar('pager', $pager);
    }
}
