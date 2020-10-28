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
            VolumeModel::remove($args['isbn']);
            return $response->withJson(['result' => 'ok'], 200);
        })->add(SlimAuthorization::IsAdmin());

        $app->patch('/api/volume/{isbn}', function(Request $request, Response $response, array $args) {
            /** @var VolumeModel $volume */
            $volume = VolumeModel::createFromArray($request->getParsedBody());
            $isbn = $args['isbn'];
            $isbnNew = $request->getParsedBodyParam('isbn');

            if ($isbnNew && $isbnNew != $isbn) {
                $volume->isbn = $isbnNew;
            }
            VolumeModel::save($volume, $isbn);

            return $response->withJson(['result' => 'ok', 'data' => VolumeModel::find($volume->isbn)], 200);
        })->add(SlimAuthorization::IsAdmin());
    }
}