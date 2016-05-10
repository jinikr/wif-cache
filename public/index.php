<?php

use \Phalcon\DI\FactoryDefault as Di;
use \Phalcon\Http\Response;
use \App\Bootstrap;
use \Peanut\Phalcon\Mvc\Micro;

try
{
    define('__BASE__', dirname(dirname(__FILE__)));

    include_once __BASE__."/vendor/autoload.php";
    include_once __BASE__.'/app/helpers/function.php';

    $bootstrap = new Bootstrap(new Di);
    $bootstrap(new Micro)->handle();
}
catch (\Exception $e)
{
    $response = new Response();
    $response
        ->setStatusCode(500)
        ->setHeader("Content-Type", "application/xml")
        ->setContent('<error>'
            .'<status>500</status>'
            .'<message>'.$e->getMessage().'</message>'
            .'</error>')
        ->send();
}
