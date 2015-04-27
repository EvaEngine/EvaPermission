<?php

namespace Eva\EvaPermission\Entities;

use Eva\EvaUser\Entities\EvaUserEntityBase;

class Operations extends EvaUserEntityBase
{
    protected $tableName = 'permission_operations';

    /**
     *
     * @var integer
     */
    public $id;

    /**
     *
     * @var string
     */
    public $name;

    /**
     *
     * @var string
     */
    public $operationKey;

    /**
     *
     * @var integer
     */
    public $resourceId;

    /**
     *
     * @var string
     */
    public $resourceKey;

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
            'operationId',
            'roleId',
            'Eva\EvaPermission\Entities\Roles',
            'id',
            array('alias' => 'roles')
        );

        $this->belongsTo("resourceId", 'Eva\EvaPermission\Entities\Resources', "id", array(
            'alias' => 'resource'
        ));
        parent::initialize();
    }
}
