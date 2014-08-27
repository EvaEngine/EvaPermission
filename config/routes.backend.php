<?php

return array(
    '/admin/permission/resource' =>  array(
        'module' => 'EvaPermission',
        'controller' => 'Admin\Resource',
    ),
    '/admin/permission/operation' =>  array(
        'module' => 'EvaPermission',
        'controller' => 'Admin\Operation',
    ),
    '/admin/permission/role' =>  array(
        'module' => 'EvaPermission',
        'controller' => 'Admin\Role',
    ),
    '/admin/permission/role/:action(/(\d+))*' =>  array(
        'module' => 'EvaPermission',
        'controller' => 'Admin\Role',
        'action' => 1,
        'id' => 3,
    ),
    '/admin/permission/apikey' =>  array(
        'module' => 'EvaPermission',
        'controller' => 'Admin\Apikey',
    ),
    '/admin/permission/apikey/:action(/(\d+))*' =>  array(
        'module' => 'EvaPermission',
        'controller' => 'Admin\Apikey',
        'action' => 1,
        'id' => 3,
    ),
    '/admin/permission/process/:action(/(\d+)*((/(\w+)/(\d+))*))*' =>  array(
        'module' => 'EvaPermission',
        'controller' => 'Admin\Process',
        'action' => 1,
        'id' => 3,
        'subaction' => 6,
        'subid' => 7,
    ),
);
