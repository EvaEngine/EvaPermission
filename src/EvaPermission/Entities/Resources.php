<?php

namespace Eva\EvaPermission\Entities;

class Resources extends \Eva\EvaEngine\Mvc\Model
{
    protected $tableName = 'permission_resources';

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
    public $resourceKey;

    /**
     *
     * @var string
     */
    public $resourceGroup;

    /**
     *
     * @var string
     */
    public $description;

    public function initialize()
    {
        $this->hasMany(
            'id',
            'Eva\EvaPermission\Entities\Operations',
            'resourceId',
            array('alias' => 'operations')
        );
    
    }
}
