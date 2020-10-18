<?php

namespace mangaslib\controllers\init;

use mangaslib\db\Library;
use mangaslib\utilities\InitializationHelper;
use sunframework\route\IRoutable;
use sunframework\SunApp;

class InstallationController implements IRoutable {
    /**
     * Register the routes of your controller.
     * @param SunApp $app The current slim app
     */
    public function registerRoute(SunApp $app) {
        $app->get('/', function($request, $response, $args) {
            $error = $request->getQueryParam('error');
            switch ($error) {
                case 'db':
                    $error = "Could not connect to the database. Did you enter the appropriate credential?";
                    break;
                case 'user':
                    $error = "Could not create a new user. Is your database properly created?";
                    break;
            }
            return $this->view->render($response, 'Initialization.twig', [
                'hasDb' => InitializationHelper::IsDatabaseInitialized(),
                'hasAdmin' => InitializationHelper::HasAdmin(),
                'error' => $error
            ]);
        });

        $app->post('/db', function($request, $response, $args) {
            $uri = $request->getParsedBodyParam('uri');
            $dbName = $request->getParsedBodyParam('dbName');
            $port = $request->getParsedBodyParam('port');
            $username = $request->getParsedBodyParam('username');
            $password = $request->getParsedBodyParam('password');

            if (Library::testConnection($uri, $username, $password, $dbName, $port)) {
                InitializationHelper::InitializeDatabaseConfig($uri, $username, $password, $dbName, $port);
                return $response->withRedirect('/');
            } else {
                return $response->withRedirect('/?error=db');
            }
        });

        $app->post('/user', function($request, $response, $args) {
            // TODO: check if not empty and blablabla
            $error = "";
            try{
                $username = $request->getParsedBodyParam('username');
                $mail = $request->getParsedBodyParam('email');
                $password =  hash('sha512', $request->getParsedBodyParam('password'));
                $lib = new Library();
                $lib->addNewUser($username, $mail, $password, 1);
            } catch (\Exception $e) {
                $error="?error=user";
            }

            return $response->withRedirect("/$error");
        });
    }
}