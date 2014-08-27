<?php

namespace Eva\EvaPermission\Entities;

class Roles extends \Eva\EvaEngine\Mvc\Model
{
    protected $tableName = 'permission_roles';

    /**
     *
     * @var integer
     */
    public $id;

    /**
     *
     * @var string
     */
    public $roleKey;

    /**
     *
     * @var string
     */
    public $name;

    /**
     *
     * @var string
     */
    public $description;


    public function initialize()
    {
        $this->hasManyToMany(
            'id',
            'Eva\EvaPermission\Entities\RolesOperations',
            'roleId',
            'operationId',
            'Eva\EvaPermission\Entities\Operations',
            'id',
            array('alias' => 'operations')
        );


        parent::initialize();
    }
}
