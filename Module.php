<?php

namespace Eva\EvaPermission;

use Phalcon\Loader;
use Phalcon\Mvc\Dispatcher;
use Phalcon\Mvc\ModuleDefinitionInterface;
use Eva\EvaEngine\Module\StandardInterface;

class Module implements ModuleDefinitionInterface, StandardInterface
{
    public static function registerGlobalAutoloaders()
    {
        return array(
            'Eva\EvaPermission' => __DIR__ . '/src/EvaPermission',
        );
    }

    public static function registerGlobalEventListeners()
    {
        return array(
            'dispatch' => 'Eva\EvaPermission\Events\DispatchListener',
            'user' => 'Eva\EvaPermission\Events\UserListener',
        );
    }

    public static function registerGlobalViewHelpers()
    {
    }

    public static function registerGlobalRelations()
    {
        return array(
            'usersRoles' => array(
                'module' => 'EvaUser',
                'entity' => 'Eva\EvaUser\Entities\Users',
                'relationType' => 'hasManyToMany',
                'parameters' => array(
                    'id',
                    'Eva\EvaPermission\Entities\UsersRoles',
                    'userId',
                    'roleId',
                    'Eva\EvaPermission\Entities\Roles',
                    'id',
                    array(
                        'alias' => 'roles'
                    )
                )
            ),
        );
    }

    /**
    * Registers the module auto-loader
    */
    public function registerAutoloaders()
    {
    }

    /**
    * Registers the module-only services
    *
    * @param Phalcon\DI $di
    */
    public function registerServices($di)
    {
        $dispatcher = $di->getDispatcher();
        $dispatcher->setDefaultNamespace('Eva\EvaPermission\Controllers');
    }
}
