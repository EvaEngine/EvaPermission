<?php

namespace Eva\EvaPermission\Entities;

use Eva\EvaUser\Entities\EvaUserEntityBase;

class UsersRoles extends EvaUserEntityBase
{
    protected $tableName = 'permission_users_roles';

    /**
     *
     * @var integer
     */
    public $userId;

    /**
     *
     * @var integer
     */
    public $roleId;


    public function initialize()
    {
        $this->belongsTo(
            'userId', 'Eva\EvaUser\Entities\Users', 'id',
            array('alias' => 'user')
        );
        $this->belongsTo(
            'roleId', 'Eva\EvaPermission\Entities\Roles', 'id',
            array('alias' => 'role')
        );

        parent::initialize();
    }
}
