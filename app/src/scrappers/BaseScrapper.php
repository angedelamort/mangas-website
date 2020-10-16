<?php

namespace mangaslib\scrappers;

abstract class BaseScrapper {

    /**
     * @param $id string id to be able to fetch the data.
     * @return array using appropriate property names.
     * property list: [genres, themes, description, comment, rating(0-10), thumbnail, scrapper_id, scrapper_mapping]
     */
    public abstract function getMangasInfoFromId($id);

    /**
     * Search the scrapper using the title
     * @param string $title The title you want to search with
     * @return array A list of items containing the title: [ [string id, array titles, string thumbnail], ... ]
     */
    public abstract function searchByTitle($title);
}