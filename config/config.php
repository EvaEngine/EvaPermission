<?php
return array(
    'permission' => array(
        'disableAll' => false,
        'superusers' => array(
            1,
        ),
        'superkeys' => array(
        ),
        'keyLevels' => array(
            'basic' => array(
                'minutelyRate' => 60,
                'hourlyRate' => 4000,
                'dailyRate' => 5000,
                'expires' => 3600 * 24 * 7,  //1 week 
            ),
            'starter' => array(
                'minutelyRate' => 100,
                'hourlyRate' => 10000,
                'dailyRate' => 30000,
                'expires' => 3600 * 24 * 30,  //1 month 
            ),
            'business' => array(
                'minutelyRate' => 60,
                'hourlyRate' => 20000,
                'dailyRate' => 100000,
                'expires' => 3600 * 24 * 90,  //3 months
            ),
            'unlimited' => array(
                'minutelyRate' => 300,
                'hourlyRate' => 30000,
                'dailyRate' => 1000000,
                'expires' => 0,
            ),
            'extreme' => array(
                'minutelyRate' => 300,
                'hourlyRate' => 40000,
                'dailyRate' => 0,
                'expires' => 0,
            ),
            'blocked' => array(
                'minutelyRate' => '-1',
                'hourlyRate' => '-1',
                'dailyRate' => '-1',
                'expires' => '-1',
            ),
        ),
        'error' => array(
            'module' => '',
            'controller' => '',
            'action' => '',
            'params' => '',
        ),
        'acl' => array(
            'adapter' => 'Memory',
        )
    ),
);
