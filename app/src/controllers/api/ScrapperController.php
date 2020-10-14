<?php

namespace mangaslib\controllers\api;

use mangaslib\db\Library;
use mangaslib\scrappers\AnilistScrapper;
use mangaslib\utilities\SlimAuthorization;
use Slim\Http\Request;
use Slim\Http\Response;
use sunframework\route\IRoutable;
use sunframework\SunApp;

class ScrapperController implements IRoutable {
    public function registerRoute(SunApp $app) {

        $app->get('/api/scrapper/{id}/search/{searchTerms}', function(Request $request, Response $response, $args) {
            if ($args['id'] == 'anilist')  {
                $scrapper = new AnilistScrapper();
                $result = $scrapper->searchByTitle($args['searchTerms']);
                $result = json_decode($result, true);

                $items = [];
                foreach ($result['data']['Page']['media'] as $media) {
                    $items[] = [
                        'id' => $media['id'],
                        'titles' => $media['title'],
                        'image' => $media['coverImage']['medium']
                    ];
                }

                return $this->view->render($response, 'partials/modal-search-results.twig', [
                        'items' => $items]
                );
            }

            return $response->withJson(['result' => 'error'], 400);
        })->add(SlimAuthorization::IsAdmin());

        // TODO: this method is weird...
        $app->get('/api/scrapper/{id}/fetch/{externalId}', function(Request $request, Response $response, $args) {
            if ($args['id'] == 'anilist')  {
                $seriesId = $request->getQueryParam('seriesId');

                $scrapper = new AnilistScrapper();
                $result = $scrapper->getMangasInfoFromId($args['externalId']);
                $json = json_decode($result, true);
                $themes = [];
                foreach ($json['data']['Media']['tags'] as $tag) {
                    $themes[] = $tag['name'];
                }

                $lib = new Library();
                $lib->addOrUpdateToScrapper([
                    'id' => $seriesId,
                    'scrapper_id' => AnilistScrapper::ID,
                    'genres' => join(',', $json['data']['Media']['genres']),
                    'themes' => join(',', $themes),
                    'description' => $json['data']['Media']['description'],
                    'comment' => $result,
                    'rating' => 0,
                    'thumbnail' => $json['data']['Media']['coverImage']['large'],
                    'scrapper_mapping' => $args['id']
                ]);

                return $response->withJson(['result' => 'ok'], 200);
            }

            return $response->withJson(['result' => 'error'], 400);
        })->add(SlimAuthorization::IsAdmin());
    }
}