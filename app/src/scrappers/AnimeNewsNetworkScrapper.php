<?php

include_once "BaseScrapper.php";
/*
// http://www.animenewsnetwork.com/encyclopedia/api.php
class AnimeNewsNetworkScrapper extends BaseScrapper {

    private $mangasId;

    public function __construct($mangasId)
    {
        $this->mangasId = "$mangasId";
    }

    // TODO: add a table scrapper to store the information
    // TODO: make a button that ask for an id and scrape to import images (in cache folder) and other stuff.
    public function getMangasInfoFromId($id) {
        $result = [];
        $url = "http://cdn.animenewsnetwork.com/encyclopedia/api.xml?manga=$id";
        $content = file_get_contents($url);
        $xml = simplexml_load_string($content);

        // name
        $mangaXml = $xml->xpath('//manga');
        if (sizeof($mangaXml) > 0) {
            $result['name'] = (string)$mangaXml[0]['name'];
        }

        // genres
        $genresXml = $xml->xpath('//info[@type=\'Genres\']');
        foreach ($genresXml as $elem) {
            $result['genres'][] = (string)$elem;
        }

        // themes
        $genresXml = $xml->xpath('//info[@type=\'Themes\']');
        foreach ($genresXml as $elem) {
            $result['themes'][] = (string)$elem;
        }

        // description
        $summary = $xml->xpath('//info[@type=\'Plot Summary\']');
        if (sizeof($summary) > 0) {
            $result['description'] = (string)$summary[0];
        }

        // rating
        $ratings = $xml->xpath('//ratings');
        if (sizeof($ratings) > 0) {
            $score = floatval((string)$ratings[0]['bayesian_score']);
            $result['rating'] = $score;
        }

        // thumbnail
        $pictureXml = $xml->xpath('//info[@type=\'Picture\']');
        if (sizeof($pictureXml) > 0) {
            $result['thumbnail'] = (string)$pictureXml[0]['src'];
        }

        // SML: should I cache the image and rewrite the thumbnail path.?

        $result = [
            "items" => $result,
            "id" => $this->mangasId,
            "scrapper-id" => "ann",
            "scrapper-mapping" => "$url"
        ];

        return $result;
    }
}*/