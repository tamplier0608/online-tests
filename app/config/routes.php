<?php

return array(
    'config.routes' => array(
        array(
            'name' => 'homepage',
            'pattern' => '/',
            'controller' => 'AppBundle\Controller\IndexController::indexAction',
            'method' => 'get',
        ),
        array(
            'name' => 'test_page',
            'pattern' => '/test/{id}',
            'controller' => 'AppBundle\Controller\IndexController::doTestAction',
            'method' => 'get',
            'assert' => array('id' => '^[\d]+$'),
        ),
        array(
            'name' => 'test_processing',
            'pattern' => '/test/{id}',
            'controller' => 'AppBundle\Controller\IndexController::processTestAction',
            'method' => 'post',
            'assert' => array('id' => '^[\d]+$'),
        ),
        array(
            'name' => 'result_page',
            'pattern' => '/test/{id}/result',
            'controller' => 'AppBundle\Controller\IndexController::finishTestAction',
            'method' => 'get',
            'assert' => array('id' => '^[\d]+$'),
        ),
        array(
            'name' => 'test_cancel',
            'pattern' => '/test/{id}/cancel',
            'controller' => 'AppBundle\Controller\IndexController::cancelTestAction',
            'method' => 'get',
            'assert' => array('id' => '^[\d]+$'),
        ),

        array(
            'name' => 'category_show',
            'pattern' => '/cat/{id}',
            'controller' => 'AppBundle\Controller\IndexController::showCategoryAction',
            'method' => 'get',
            'assert' => array('id' => '^[\d]+$'),
        ),

        # user routes
        array(
            'name' => 'user_login',
            'pattern' => '/login',
            'controller' => 'AppBundle\Controller\UserController::loginAction',
            'method' => array('get', 'post'),
        ),

        array(
            'name' => 'user_signin',
            'pattern' => '/sign-in',
            'controller' => 'AppBundle\Controller\UserController::signinAction',
            'method' => array('get', 'post'),
        ),

        array(
            'name' => 'user_logout',
            'pattern' => '/logout',
            'controller' => 'AppBundle\Controller\UserController::logoutAction',
            'method' => 'get',
        ),

        array(
            'name' => 'user_cabinet',
            'pattern' => '/user/cabinet',
            'controller' => 'AppBundle\Controller\UserController::cabinetAction',
            'method' => 'get',
        ),

        array(
            'name' => 'user_activation',
            'pattern' => 'user/{id}/activation/{code}',
            'controller' => 'AppBundle\Controller\UserController::activationAction',
            'method' => 'get'
        ),

        array(
            'name' => 'user_purchase',
            'pattern' => 'user/buy/{test_id}',
            'controller' => 'AppBundle\Controller\UserController::buyAction',
            'method' => 'get'
        ),

        array(
            'name' => 'user_addcredit',
            'pattern' => 'user/addcredit',
            'controller' => 'AppBundle\Controller\UserController::addcreditAction',
            'method' => array('get', 'post')
        ),

        array(
            'name' => 'user_clean_data',
            'pattern' => 'user/clean-data/{username}',
            'controller' => 'AppBundle\Controller\UserController::cleanData',
            'method' => 'get'
        )
    ),

);