<?php

namespace mangaslib\scrappers;

// http://www.animenewsnetwork.com/encyclopedia/api.php
use mangaslib\models\SeriesModel;

class AnimeNewsNetworkScrapper extends BaseScrapper {

    public const ID = "ann";

    // https://www.animenewsnetwork.com/encyclopedia/reports.xml?id=155&type=manga&search=heart&nlist=50
    public function searchByTitle($title) {
        $title = rawurlencode($title);
        $url = "https://www.animenewsnetwork.com/encyclopedia/reports.xml?id=155&type=manga&search=${title}&nlist=20";
        $content = file_get_contents($url);
        $xml = simplexml_load_string($content);

        $result = [];
        foreach ($xml->item as $item) {
            error_log($item->name);
            $result[] = [
                'id' => $item->id,
                'titles' => ['en' => $item->name],
                'image' => '/android-chrome-192x192.png'
            ];
        }

        return $result;
    }

    public function createSeriesFromId(string $id) : SeriesModel {
        $result = [];
        $url = "http://cdn.animenewsnetwork.com/encyclopedia/api.xml?manga=$id";
        error_log($url);
        $content = file_get_contents($url);
        $xml = simplexml_load_string($content);

        $mangaXml = $xml->xpath('//manga');
        if (sizeof($mangaXml) > 0) {
            $result['name'] = (string)$mangaXml[0]['name'];
        }

        // genres
        $genresXml = $xml->xpath('//info[@type=\'Genres\']');
        $result['genres'] = [];
        foreach ($genresXml as $elem) {
            $result['genres'][] = (string)$elem;
        }

        // themes
        $genresXml = $xml->xpath('//info[@type=\'Themes\']');
        $result['themes'] = [];
        foreach ($genresXml as $elem) {
            $result['themes'][] = (string)$elem;
        }

        // description
        $summary = $xml->xpath('//info[@type=\'Plot Summary\']');
        if (sizeof($summary) > 0) {
            $result['description'] = (string)$summary[0];
        } else {
            $result['description'] = '';
        }

        // rating
        $ratings = $xml->xpath('//ratings');
        if (sizeof($ratings) > 0) {
            $score = floatval((string)$ratings[0]['bayesian_score']);
            $result['rating'] = $score;
        } else {
            $result['rating'] = 0;
        }

        // thumbnail
        $pictureXml = $xml->xpath('//info[@type=\'Picture\']');
        if (sizeof($pictureXml) > 0) {
            $result['thumbnail'] = (string)$pictureXml[0]['src'];
        } else {
            $result['thumbnail'] = '';
        }

        // TODO: get alternative titles

        $series = new SeriesModel();
        $series->title = $result['name'];
        $series->genres = join(',', $result['genres']);
        $series->themes = join(',', $result['themes']);
        $series->synopsis = $result['description'];
        $series->rating = $result['rating'];
        $series->thumbnail = $result['thumbnail'];

        return $series;
    }
}