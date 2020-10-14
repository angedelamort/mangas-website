<?php

namespace mangaslib\controllers;

use Slim\Http\Request;
use Slim\Http\Response;
use sunframework\route\IRoutable;
use sunframework\SunApp;
use sunframework\user\UserSession;
use mangaslib\db\Library;


class HomeController implements IRoutable {
    public function registerRoute(SunApp $app) {
        $app->get('/', function($request, $response, $args) {
            $lib = new Library();
            $series = $lib->getAllSeries(true);
            return $this->view->render($response, 'home.twig', [
                'series' => $series
            ]);
        });

        $app->get('/statistics', function($request, $response, $args) {
            $lib = new Library();
            return $this->view->render($response, 'statistics.twig', [
                'series' => [
                    'count' => $lib->getSeriesCount(),
                    'completed' => $lib->getSeriesCompletedCount()
                ],
                'volume' => [
                    'count' => $lib->getVolumeCount(),
                    'recentlyAdded' => $lib->getLatestVolumes(10)
                ]
            ]);
        });

        $app->get('/missing-mangas', function($request, $response, $args) {
            $lib = new Library();
            $items = $lib->getMissingMangas();
            return $this->view->render($response, 'missing-mangas.twig', [
                'series' => $items
            ]);
        });

        // Move to UserController
        $app->get('/login', function($request, $response, $args) {
            return $this->view->render($response, 'login.twig');
        });

        $app->post('/login', function(Request $request, Response $response, array $args) use ($app) {
            $email = $request->getParsedBodyParam('email');
            $password = $request->getParsedBodyParam('password');
            $password = hash('sha512', $password);
            $referer = $request->getQueryParam('referer');
            if (!$referer) {
                $referer = '/';
            }

            $lib = new Library();
            $user = $lib->findUser($email, $password);

            $app->getAuthManager()->setUser($user);
            if ($user['rolw'] == 1) {
                $app->getAuthManager()->setUserRoles(UserSession::ROLE_ADMIN);
            }

            return $response->withRedirect($referer);
        });

        $app->get('/logout', function(Request $request, Response $response, $args) use ($app) {
            $referer = $request->getQueryParam('referer');
            if (!$referer) {
                $referer = '/';
            }

            $app->getAuthManager()->setUser(null);
            $app->getAuthManager()->setUserRoles(null);
            return $response->withRedirect($referer);
        });

        $app->get('/show-page/{id}[/{name}]', function(Request $request, Response $response, array $args) {
            $lib = new Library();
            $series = $lib->findSeriesById($args['id']);
            $series = $lib->populateExtraDataToSeries($series);

            $volumes = $lib->getAllVolumes($args['id']);

            return $this->view->render($response, 'show-page.twig', [
                "series" => $series,
                "volumes" => $volumes
            ]);
        });
    }
}