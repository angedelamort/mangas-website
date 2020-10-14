<?php

namespace mangaslib\scrappers;

abstract class BaseScrapper {

    /**
     * @param $id string id to be able to fetch the data.
     * @return array using appropriate property names.
     * property list: [genres, themes, description, rating(0-10), thumbnail]
     */
    public abstract function getMangasInfoFromId($id);

    /**
     * Search the scrapper using the title
     * @param $title The title you want to search with
     * @return A list of items containing the title: [ [id, title, thumbnail], ... ]
     */
    public abstract function searchByTitle($title);
}