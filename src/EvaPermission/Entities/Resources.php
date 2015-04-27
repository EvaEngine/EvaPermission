<?php

namespace Eva\EvaPermission\Entities;

use Eva\EvaUser\Entities\EvaUserEntityBase;

class Resources extends EvaUserEntityBase
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
