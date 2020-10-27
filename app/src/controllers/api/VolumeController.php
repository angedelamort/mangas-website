<?php

namespace mangaslib\controllers\api;

use mangaslib\models\VolumeModel;
use mangaslib\utilities\SlimAuthorization;
use Slim\Http\Request;
use Slim\Http\Response;
use sunframework\route\IRoutable;
use sunframework\SunApp;

class VolumeController implements IRoutable {
    public function registerRoute(SunApp $app) {

        $app->delete('/api/volume/{isbn}', function(Request $request, Response $response, array $args) {
            VolumeModel::delete($args['isbn']);
            return $response->withJson(['result' => 'ok'], 200);
        })->add(SlimAuthorization::IsAdmin());

        $app->patch('/api/volume/{isbn}', function(Request $request, Response $response, array $args) {
            $isbnNew = $request->getParsedBodyParam('isbn');
            $volume = intval($request->getParsedBodyParam('volume'));
            $lang = $request->getParsedBodyParam('lang');
            // TODO: probably send the bodyparams? and initialize?
            VolumeModel::update($args['isbn'], $isbnNew, $volume, $lang);

            return $response->withJson(['result' => 'ok'], 200);
        })->add(SlimAuthorization::IsAdmin());
    }
}