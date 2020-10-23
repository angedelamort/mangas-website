<?php

namespace mangaslib\controllers;

use mangaslib\db\Library;
use mangaslib\utilities\SlimAuthorization;
use Slim\Http\Request;
use Slim\Http\Response;
use sunframework\route\IRoutable;
use sunframework\SunApp;


class UserController implements IRoutable {

    /**
     * Register the routes of your controller.
     * @param SunApp $app The current slim app
     */
    public function registerRoute(SunApp $app)
    {
        $app->get('/user', function(Request $request, Response $response, array $args) {
            return $this->view->render($response, 'user.twig', [
            ]);
        })->add(SlimAuthorization::IsAdmin());
    }
}
