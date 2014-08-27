<?php
namespace Eva\EvaPermission\Forms;

use Eva\EvaEngine\Form;
use Phalcon\Forms\Element\Select;

class ApikeyForm extends Form
{
    /**
     *
     * @Type(Hidden)
     * @var integer
     */
    public $id;

    /**
     *
     * @Type(Hidden)
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
     * @Type(Select)
     * @Option(basic=Basic)
     * @Option(starter=Starter)
     * @Option(business=Business)
     * @Option(unlimited=Unlimited)
     * @Option(extreme=Extreme)
     * @Option(customize=Customize)
     * @Option(blocked=Blocked)
     * @var string
     */
    public $level = 'basic';

    /**
     *
     * @var integer
     */
    public $minutelyRate;

    /**
     *
     * @var integer
     */
    public $hourlyRate;

    /**
     *
     * @var integer
     */
    public $dailyRate;

    /**
     *
     * @var integer
     */
    public $createdAt;

    /**
     *
     * @Type(Hidden)
     * @var integer
     */
    public $expiredAt;

    /**
     *
     * @var string
     */
    public $username;

    public function initialize($entity = null, $options = null)
    {
        $this->initializeFormAnnotations();
    }
}
