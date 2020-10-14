<?php

require('../vendor/autoload.php');

use sunframework\SunApp;
use sunframework\SunAppConfig;
use sunframework\twigExtensions\LibraryExtension;
use sunframework\twigExtensions\LibraryItem;
use mangaslib\extensions\SiteTwigExtension;
use mangaslib\extensions\LinkTwigExtension;

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

// TODO: don't like the way we enable the routes.
$config->activateRoutes([
    'mangaslib\controllers' => dirname(__DIR__) . '/app/src/controllers',
    'mangaslib\controllers\api' => dirname(__DIR__) . '/app/src/controllers/api'
]);
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
