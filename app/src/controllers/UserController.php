<?php

namespace mangaslib\controllers;

use mangaslib\models\SeriesModel;
use mangaslib\models\UserModel;
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
            /** @var UserModel $user */
            $user = $session->getUser();
            return $this->view->render($response, 'user.twig', [
                "wishlist" => json_encode($user->wishlist(), JSON_PRETTY_PRINT)
            ]);
        })->add(SlimAuthorization::IsAdmin());

        $app->get('/user/login', function($request, $response, $args) {
            return $this->view->render($response, 'login.twig');
        });

        $app->post('/user/login', function(Request $request, Response $response, array $args) use ($app) {
            $email = $request->getParsedBodyParam('email');
            $password = $request->getParsedBodyParam('password');
            $password = hash('sha512', $password);
            $referer = $request->getQueryParam('referer');
            if (!$referer) {
                $referer = '/';
            }

            $user = UserModel::find($email, $password);
            if ($user && $user->role == 1) {
                $app->getAuthManager()->setUser($user);
                $app->getAuthManager()->setUserRoles(UserSession::ROLE_ADMIN);
                return $response->withRedirect($referer);
            }
            return $response->withRedirect('user/login'); // TODO: write some kind of errors in the login page.
        });

        $app->get('/user/logout', function(Request $request, Response $response, $args) use ($app) {
            $referer = $request->getQueryParam('referer');
            if (!$referer) {
                $referer = '/';
            }

            $app->getAuthManager()->setUser(null);
            $app->getAuthManager()->setUserRoles(null);
            return $response->withRedirect($referer);
        });

        $app->get('/user/wishlist', function(Request $request, Response $response, $args) use ($app) {
            $session = new UserSession();
            $user = UserModel::find($session->getUser()->email);

            return $this->view->render($response, 'wishlist.twig', [
                "wishlist" => $user->wishlist()
            ]);
        });

        // TODO: return JSON should be under wishlist API.
        $app->patch('/user/wishlist', function(Request $request, Response $response, array $args) {
            $session = new UserSession();
            $user = UserModel::find($session->getUser()->email);
            $user->updateWishlist($request->getParsedBody());
            return $response->withJson(['result' => 'ok'], 200);
        })->add(SlimAuthorization::IsAdmin());

        $app->get('/user/missing-mangas', function($request, $response, $args) {
            return $this->view->render($response, 'missing-mangas.twig', [
                'missingSeries' => SeriesModel::incomplete()
            ]);
        });
    }
}
