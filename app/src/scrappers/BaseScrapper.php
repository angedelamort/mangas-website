<?php

namespace mangaslib\scrappers;

use mangaslib\models\SeriesModel;

abstract class BaseScrapper {

    /**
     * Search the scrapper using the title
     * @param string $title The title you want to search with
     * @return array A list of items containing the title: [ [string id, array titles, string thumbnail], ... ]
     */
    public abstract function searchByTitle($title);

    /**
     * @param $resourceId
     * @return SeriesModel
     */
    public abstract function createSeriesFromId(string $resourceId) : SeriesModel;
}