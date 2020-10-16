<?php

namespace mangaslib\controllers\api;

use Exception;
use mangaslib\db\Library;
use mangaslib\scrappers\AnilistScrapper;
use mangaslib\scrappers\ScrapperFactory;
use mangaslib\utilities\SlimAuthorization;
use Slim\Http\Request;
use Slim\Http\Response;
use sunframework\route\IRoutable;
use sunframework\SunApp;

// NOTE: Really weird API... would need something better in the long run.
class ScrapperController implements IRoutable {
    public function registerRoute(SunApp $app) {

        $app->get('/api/scrapper/{id}/{searchTerms}', function(Request $request, Response $response, $args) {
            try {
                $scrapper = ScrapperFactory::createFromId($args['id']);
                $items = $scrapper->searchByTitle($args['searchTerms']);
                return $this->view->render($response, 'partials/modal-search-results.twig', [
                        'items' => $items,
                        'scrapperId' => $args['id']
                    ]
                );
            } catch (Exception $e) {
                error_log($e->getMessage());
                return $response->withJson(['result' => 'error'], 400);
            }
        })->add(SlimAuthorization::IsAdmin());

        $app->post('/api/scrapper/{id}', function(Request $request, Response $response, $args) {
            $seriesId = $request->getParsedBodyParam('seriesId');
            $externalId = $request->getParsedBodyParam('externalId');

            try {
                $scrapper = ScrapperFactory::createFromId($args['id']);
                $result = $scrapper->getMangasInfoFromId($externalId);
                $result['id'] = $seriesId; // add the series ID to the array.

                $lib = new Library();
                $lib->addOrUpdateToScrapper($result);

                return $response->withJson(['result' => 'ok'], 200);
            } catch (Exception $e) {
                error_log($e->getMessage());
                return $response->withJson(['result' => 'error'], 400);
            }
        })->add(SlimAuthorization::IsAdmin());
    }
}