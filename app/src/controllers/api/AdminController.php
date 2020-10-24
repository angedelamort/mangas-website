<?php

namespace mangaslib\controllers\api;

use mangaslib\db\Library;
use mangaslib\scrappers\ScrapperFactory;
use mangaslib\utilities\SlimAuthorization;
use Slim\Http\Request;
use Slim\Http\Response;
use sunframework\route\IRoutable;
use sunframework\SunApp;

class AdminController implements IRoutable {
    public function registerRoute(SunApp $app) {

        $app->post('/api/admin/cron/scrapper/{id}', function(Request $request, Response $response, array $args) {
            $messages = [];
            $lib = new Library();
            $scrapperId = $args['id'];
            $scrapper = ScrapperFactory::createFromId($scrapperId);
            $series = $lib->getAllSeries();

            foreach ($series as $item) {
                // TODO: I don't like the flow of this - get scrapper data from ic ans do a json2model...
                $messages[] = "processing $item[title][en]...";
                $scrapperData = $lib->getScrapperData($scrapperId, $item['id']); // TODO: this is weird to pass the scrapperId... I think the scrapper should get the appropriate data.
                $model = $scrapper->JsonToModel($scrapperData['comment']);

                $toUpdate = [];
                if (!$item['volumes'] && $model['volumes']) $toUpdate['volumes'] = intval($model['volumes']);
                if (!$item['chapters'] && $model['chapters']) $toUpdate['chapters'] = intval($model['chapters']);
                if (!$item['cover'] && $model['cover']) $toUpdate['cover'] = $model['cover'];
                if (!$item['banner'] && $model['banner']) $toUpdate['banner'] = $model['banner'];
                if (!$item['alternate_titles'] && $model['alternate_titles']) $toUpdate['alternate_titles'] = json_encode($model['alternate_titles']);
                if (!$item['series_status'] && $model['status']) $toUpdate['series_status'] = ($model['status'] == 'FINISHED') ? 1 : 0;

                $result = $lib->updateSeries($item['id'], $toUpdate);
                $messages[] = ($result) ? "...database updated $item[title]" : "...nothing to update";
            }

            return $this->view->render($response, 'user.twig', [
                'messages' => $messages,
                'command' => 'UpdateSeriesFromAnilist'
            ]);
        })->add(SlimAuthorization::IsAdmin());
    }
}