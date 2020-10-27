<?php

namespace mangaslib\controllers\api;

use mangaslib\models\SeriesModel;
use mangaslib\models\VolumeModel;
use mangaslib\utilities\SeoHelper;
use mangaslib\utilities\SlimAuthorization;
use Slim\Http\Request;
use Slim\Http\Response;
use sunframework\route\IRoutable;
use sunframework\SunApp;

class SeriesController implements IRoutable {
    public function registerRoute(SunApp $app) {

        $app->post('/api/series', function(Request $request, Response $response, $args) {
            error_log(json_encode($request->getParsedBody()));
            $title = $request->getParsedBodyParam('title');

            if ($title) {
                // TODO: just send the $request->getParsedBody() and the add would just find the appropriate fields.
                $series = SeriesModel::add([
                    'title' => $title,
                    'short_name' => $request->getParsedBodyParam('short_name')
                ]);
                return $response->withJson([
                    'result' => 'ok',
                    'data'=> $series,
                    'redirectUrl' => "/show-page/$series->id/" . SeoHelper::normalizeTitle($series->title)
                ], 200);
            }

            return $response->withJson(['result' => 'error'], 400);
        })->add(SlimAuthorization::IsAdmin());

        $app->delete('/api/series/{id}', function(Request $request, Response $response, $args) {
            SeriesModel::delete($args['id']);
            return $response->withJson(['result' => 'ok'], 200);
        })->add(SlimAuthorization::IsAdmin());

        $app->patch('/api/series/{id}', function(Request $request, Response $response, array $args) {
            $series = $request->getParsedBody();
            $series['id'] = $args['id'];
            SeriesModel::update($series);
            return $response->withJson(['result' => 'ok'], 200);
        })->add(SlimAuthorization::IsAdmin());

        $app->post('/api/series/{id}/volume', function(Request $request, Response $response, $args) {
            $volume = new VolumeModel();
            $volume->title_id = intval($args['id']);
            $volume->isbn = $request->getParsedBodyParam('isbn');
            $volume->volume = intval($request->getParsedBodyParam('volume'));
            $volume->lang = $request->getParsedBodyParam('lang');

            // TODO: the if should be in the add and not in here...
            if ($volume->title_id !== FALSE && $volume->isbn && $volume->volume !== FALSE && $volume->lang) {
                // TODO: use the $request->getParsedBody() directly
                $volume = VolumeModel::add($volume);
                return $response->withJson(['result' => 'ok', 'data' => json_encode($volume)], 200);
            }

            return $response->withJson(['result' => 'error'], 400);
        })->add(SlimAuthorization::IsAdmin());
    }
}