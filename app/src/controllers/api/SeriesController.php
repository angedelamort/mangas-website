<?php

namespace mangaslib\controllers\api;

use mangaslib\db\Library;
use mangaslib\utilities\SlimAuthorization;
use Slim\Http\Request;
use Slim\Http\Response;
use sunframework\route\IRoutable;
use sunframework\SunApp;

class SeriesController implements IRoutable {
    public function registerRoute(SunApp $app) {

        $app->post('/api/series', function(Request $request, Response $response, $args) {
            $name = $request->getParsedBodyParam('name');

            if ($name) {
                $lib = new Library();
                $result = $lib->addSeries($name);
                return $response->withJson([
                    'result' => 'ok',
                    'data'=> $result
                ], 200);
            }

            return $response->withJson(['result' => 'error'], 400);
        })->add(SlimAuthorization::IsAdmin());

        $app->delete('/api/series/{id}', function(Request $request, Response $response, $args) {
            $lib = new Library();
            $lib->DeleteSeries($args['id']);
            return $response->withJson(['result' => 'ok'], 200);
        })->add(SlimAuthorization::IsAdmin());

        $app->patch('/api/series/{id}', function(Request $request, Response $response, $args) {
            $lib = new Library();
            error_log($args['id']);
            $lib->updateSeries($args['id'], $request->getParsedBody());
            return $response->withJson(['result' => 'ok'], 200);
        })->add(SlimAuthorization::IsAdmin());

        $app->post('/api/series/{id}/volume', function(Request $request, Response $response, $args) {
            $id = intval($args['id']);
            $isbn = $request->getParsedBodyParam('isbn');
            $volume = intval($request->getParsedBodyParam('volume'));
            $lang = $request->getParsedBodyParam('lang');

            if ($id !== FALSE && $isbn && $volume !== FALSE && $lang) {
                $lib = new Library();
                $lib->AddVolume($id, $isbn, $volume, $lang);
                return $response->withJson(['result' => 'ok'], 200);
            }

            return $response->withJson(['result' => 'error'], 400);
        })->add(SlimAuthorization::IsAdmin());
    }
}