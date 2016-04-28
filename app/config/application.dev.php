<?php

return array(
    'db.options' => array(
        /*
        'driver'   => 'pdo_sqlite',
        'path'     => __DIR__ . '/psycho_tests.db', # path to database
        */

        'driver' => 'pdo_mysql',
        'user' => 'p_tests',
        'password' => 'password',
        'host' => 'localhost',
        'dbname' => 'psycho_tests',
        'charset' => 'utf8'
    ),

    'swiftmailer.options' => array(
        'host' => 'mail.vseoweb.in.ua',
        'port' => '25',
        'username' => 'psycho.tests',
        'password' => 'AMNSfn39X',
        'encryption' => null,
        'auth_mode' => null
    ),

    'salt' => 'asdfal457889789akjnsdfui6gb!&*^TGKG'
);