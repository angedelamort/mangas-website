<?php

namespace mangaslib\controllers\api;

use mangaslib\db\Library;
use mangaslib\utilities\SlimAuthorization;
use Slim\Http\Request;
use Slim\Http\Response;
use sunframework\route\IRoutable;
use sunframework\SunApp;

class UserController implements IRoutable {
    public function registerRoute(SunApp $app) {

        $app->patch('/api/user/{email}', function(Request $request, Response $response, array $args) {
            $lib = new Library();
            $data = $request->getParsedBody();
            $lib->updateWishlist($args['email'], $data);
            return $response->withJson(['result' => 'ok'], 200);
        })->add(SlimAuthorization::IsAdmin());
    }
}