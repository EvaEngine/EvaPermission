<?php
namespace Eva\EvaPermission\Forms;

use Eva\EvaEngine\Form;
use Phalcon\Forms\Element\Select;
use Eva\EvaPermission\Entities;

class OperationFilterForm extends Form
{
    /**
    * @Type(Hidden)
    * @var integer
    */
    public $rid;

    /**
    *
    * @var string
    */
    public $q;

    /**
    *
    * @Type(Select)
    * @Option("All Status")
    * @Option(deleted=Deleted)
    * @Option(draft=Draft)
    * @Option(pending=Pending)
    * @Option(published=Published)
    * @var string
    */
    public $status;

    public $group;

    protected $roleid;

    public function addRole()
    {
        if ($this->roleid) {
            return $this->roleid;
        }

        $roles = Entities\Roles::find();
        $options = array('All Roles');
        if ($roles) {
            foreach ($roles as $role) {
                $options[$role->id] = $role->roleKey . ' | ' . $role->name;
            }
        }
        $element = new Select('roleid', $options);
        $this->add($element);

        return $this->roleid = $element;
    }

    public function addResourceGroup()
    {
        if ($this->group) {
            return $this->group;
        }

        $groups = Entities\Resources::find(array(
            'columns' => array('resourceGroup'),
            'group' => 'resourceGroup'
        ));
        $options = array('All Groups');
        if ($groups) {
            foreach ($groups as $group) {
                $options[$group->resourceGroup] = $group->resourceGroup;
            }
        }
        $element = new Select('group', $options);
        $this->add($element);
        return $this->group = $element;
    }

    public function initialize($entity = null, $options = null)
    {
        $this->initializeFormAnnotations();
        $this->addRole();
        $this->addResourceGroup();
    }
}
