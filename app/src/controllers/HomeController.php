<?php

namespace mangaslib\controllers;

use mangaslib\models\BaseModel;
use mangaslib\models\FieldSchema;
use mangaslib\models\SeriesModel;
use mangaslib\models\VolumeModel;
use mangaslib\scrappers\ScrapperFactory;
use mangaslib\utilities\SlimAuthorization;
use ReflectionClass;
use ReflectionProperty;
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
                    'count' => SeriesModel::size(),
                    'completed' => SeriesModel::size(true)
                ],
                'volume' => [
                    'count' => VolumeModel::size(),
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

        $app->get('/scrapper/3WayMerge', function(Request $request, Response $response, array $args) {
            $scrapperId = $request->getQueryParam('scrapperId');
            $resourceId = $request->getQueryParam('resourceId');
            $seriesId = $request->getQueryParam('seriesId');

            $originalSeries = SeriesModel::find($seriesId);
            $scrapper = ScrapperFactory::createFromId($scrapperId);
            $scrapperSeries = $scrapper->createSeriesFromId($resourceId);
            $mergeSeries = self::merge($originalSeries, $scrapperSeries);

            return $this->view->render($response, 'scrapper-merge.twig', [
                'original' => $originalSeries,
                'merge' => $mergeSeries,
                'scrapper' => $scrapperSeries,
                'fields' => self::getFields()
            ]);
        })->add(SlimAuthorization::IsAdmin());
    }

    private static function getFields() {
        $reflect = new ReflectionClass(SeriesModel::class);
        $array = [];
        foreach ($reflect->getProperties(ReflectionProperty::IS_PUBLIC) as $prop) {
            $schema = new FieldSchema($reflect, $prop);
            if ($schema->isReadOnly()) {
                continue;
            }
            $array[] = [
                'name' => $prop->getName(),
                'editor' => $schema->getEditor()
            ];
        }
        return $array;
    }

    private static function merge(SeriesModel $original, SeriesModel $changes) {
        $merge = new SeriesModel();
        $reflect = new ReflectionClass(SeriesModel::class);
        foreach ($reflect->getProperties(ReflectionProperty::IS_PUBLIC) as $prop) {
            $originalValue = $prop->getValue($original);
            $changeValue = $prop->getValue($changes);
            $prop->setValue($merge, $originalValue !== null ? $originalValue : $changeValue);

        }
        return $merge;
    }
}