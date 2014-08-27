<?php
namespace Eva\EvaPermission\Forms;

use Eva\EvaEngine\Form;
use Phalcon\Forms\Element\Select;

class RoleForm extends Form
{
    /**
     * @Type(Hidden)
     * @var integer
     */
    public $id;

    /**
     * @Validator("PresenceOf", message = "Please input role name")
     * @var string
     */
    public $name;

    /**
     * @Validator("Regex", pattern = "/[A-Z_]+/", message = "Role key only allow uppercase letters and underlined")
     * @Validator("PresenceOf", message = "Please input role name")
     * @var string
     */
    public $roleKey;

    /**
     * @Type(Textarea)
     * @var string
     */
    public $description;

    public function initialize($entity = null, $options = null)
    {
    }
}
