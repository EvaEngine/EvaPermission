<?php

namespace Eva\EvaPermission\Entities;

class RolesOperations extends \Eva\EvaEngine\Mvc\Model
{
    protected $tableName = 'permission_roles_operations';

    /**
     *
     * @var integer
     */
    public $roleId;

    /**
     *
     * @var integer
     */
    public $operationId;

    public function initialize()
    {
        
        $this->belongsTo(
            'roleId', 'Eva\EvaPermission\Entities\Roles', 'id',
            array('alias' => 'role')
        );
        $this->belongsTo(
            'operationId', 'Eva\EvaPermission\Entities\Operations', 'id',
            array('alias' => 'operationId')
        );
    }
}
