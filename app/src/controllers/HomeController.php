<?php

namespace mangaslib\controllers;

use mangaslib\models\SeriesModel;
use mangaslib\models\VolumeModel;
use Slim\Http\Request;
use Slim\Http\Response;
use sunframework\route\IRoutable;
use sunframework\SunApp;

class HomeController implements IRoutable {

    public function registerRoute(SunApp $app) {
        $app->get('/', function($request, $response, $args) {
            return $this->view->render($response, 'home.twig', [
                'series' => SeriesModel::all()
            ]);
        });

        $app->get('/statistics', function($request, $response, $args) {
            $recentlyAdded = [];
            $latestVolumes = VolumeModel::latest(10);
            foreach ($latestVolumes as $volume) {
                /** @var VolumeModel $volume */
                $recentlyAdded[] = [
                    'date' => $volume->created_date,
                    'isbn' => $volume->isbn,
                    'volume' => $volume->volume,
                    'series' => $volume->series()
                ];
            }
            return $this->view->render($response, 'statistics.twig', [
                'series' => [
                    'count' => SeriesModel::count(),
                    'completed' => SeriesModel::count(true)
                ],
                'volume' => [
                    'count' => VolumeModel::count(),
                    'recentlyAdded' => $recentlyAdded
                ]
            ]);
        });

        $app->get('/show-page/{id}[/{name}]', function(Request $request, Response $response, array $args) {
            $series = SeriesModel::find($args['id']);

            return $this->view->render($response, 'show-page.twig', [
                "series" => $series,
                "volumes" => $series->volumes(),
                "missingVolumes" => $series->missingVolumes()
            ]);
        });
    }
}