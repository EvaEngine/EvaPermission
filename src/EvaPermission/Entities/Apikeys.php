<?php

namespace Eva\EvaPermission\Entities;

use Eva\EvaUser\Entities\EvaUserEntityBase;

/** @noinspection PhpUnnecessaryFullyQualifiedNameInspection */
class Apikeys extends EvaUserEntityBase
{
    protected $tableName = 'permission_apikeys';

    /**
     *
     * @var integer
     */
    public $id;

    /**
     *
     * @var integer
     */
    public $userId;

    /**
     *
     * @var string
     */
    public $apikey;

    /**
     *
     * @var string
     */
    public $level = 'basic';

    /**
     *
     * @var integer
     */
    public $minutelyRate = 0;

    /**
     *
     * @var integer
     */
    public $hourlyRate = 0;

    /**
     *
     * @var integer
     */
    public $dailyRate = 0;

    /**
     *
     * @var integer
     */
    public $createdAt = 0;

    /**
     *
     * @var integer
     */
    public $refreshedAt = 0;

    /**
     *
     * @var integer
     */
    public $expiredAt = 0;

    public function initialize()
    {
        $this->belongsTo(
            'userId',
            'Eva\EvaUser\Entities\Users',
            'id',
            array('alias' => 'user')
        );
        parent::initialize();
    }
}
