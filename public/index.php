<?php

$composer = require_once('../vendor/autoload.php');

use mangaslib\utilities\ConstructStatic;
use mangaslib\utilities\InitializationHelper;
use sunframework\SunApp;
use sunframework\SunAppConfig;
use sunframework\twigExtensions\LibraryExtension;
use sunframework\twigExtensions\LibraryItem;
use mangaslib\extensions\SiteTwigExtension;
use mangaslib\extensions\LinkTwigExtension;

// TODO: maybe move that into the framework?
$loader = new ConstructStatic($composer);

/**
 * Adding public libraries
 */
LibraryExtension::addLibrary((new LibraryItem('jquery'))
    ->addJs('https://code.jquery.com/jquery-3.5.1.js')
    ->addJsMin('https://code.jquery.com/jquery-3.5.1.min.js'));

LibraryExtension::addLibrary((new LibraryItem('semantic-ui'))
    ->addCssMin('https://cdnjs.cloudflare.com/ajax/libs/semantic-ui/2.4.1/semantic.min.css')
    ->addJsMin('https://cdnjs.cloudflare.com/ajax/libs/semantic-ui/2.4.1/semantic.min.js'));

LibraryExtension::addLibrary((new LibraryItem('datatable'))
    ->addJsMin('https://cdn.datatables.net/1.10.22/js/jquery.dataTables.min.js')
    ->addJsMin('https://cdn.datatables.net/1.10.22/js/dataTables.semanticui.min.js'));

/**
 * Initializing the application
 */
$config = new SunAppConfig();

if (InitializationHelper::IsInitialized()) {
    $config->activateRoutes([
        'mangaslib\controllers' => dirname(__DIR__) . '/app/src/controllers',
        'mangaslib\controllers\api' => dirname(__DIR__) . '/app/src/controllers/api'
    ]);
} else {
    $config->activateRoutes([
        'mangaslib\controllers\init' => dirname(__DIR__) . '/app/src/controllers/init'
    ]);
}

$config->activateTwig(dirname(__DIR__) . '/app/templates', function($view) {
    $view->addExtension(new SiteTwigExtension());
    $view->addExtension(new LinkTwigExtension());
});
$config->activateSession();

try {
    $app = new SunApp($config);
    $app->run();
} catch (Exception $e) {
    error_log($e->getMessage());
} catch (Throwable $e) {
    error_log($e->getMessage());
}
