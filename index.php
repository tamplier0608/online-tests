<?php
ini_set('display_errors', true);
error_reporting(E_ALL);
require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/app/Application.php';

defined('ENV') || define('ENV', 'dev');

$app = new \Application(array('config' => __DIR__ . '/app/config/application.' . ENV . '.php'));

$app['debug'] = true; # enable or disable debug mode

if (true === $app['debug']) {
    ini_set('display_errors', true);
    error_reporting(E_ALL);
}

# just for IDE support of Silex Framework
$app->register(new Sorien\Provider\PimpleDumpProvider());

$app->error(function (\Exception $e, $code) use($app) {
    if ($app['debug']) {
        return;
    }

    switch($code) {
        case '404':
            $message = $app['translator']->trans('error.404');
            break;

        default:
            $message = $app['translator']->trans('error.other');
            break;
    }

    return $app['twig']->render('errors/error.' . $code . '.html.twig', array(
        'message' => $message,
        'code' => $code
    ));
});

$app->run();

if ($app['debug']) {
    echo '<pre>';
    var_dump($app['session']->get('test-1'));
    var_dump($app['session']->get('test-2'));
    var_dump($app['session']->get('ref_uri'));
    echo '</pre>';
}
