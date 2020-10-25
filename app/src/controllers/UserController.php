<?php

namespace mangaslib\controllers;

use mangaslib\db\Library;
use mangaslib\utilities\SlimAuthorization;
use Slim\Http\Request;
use Slim\Http\Response;
use sunframework\route\IRoutable;
use sunframework\SunApp;
use sunframework\user\UserSession;


class UserController implements IRoutable {

    /**
     * Register the routes of your controller.
     * @param SunApp $app The current slim app
     */
    public function registerRoute(SunApp $app)
    {
        $app->get('/user', function(Request $request, Response $response, array $args) {
            $session = new UserSession();
            $email = $session->getUser()['email'];
            $lib = new Library();
            return $this->view->render($response, 'user.twig', [
                "wishlist" => json_encode($lib->getWishlist($email), JSON_PRETTY_PRINT)
            ]);
        })->add(SlimAuthorization::IsAdmin());
    }
}
