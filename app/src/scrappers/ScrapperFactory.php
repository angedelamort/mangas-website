<?php

namespace mangaslib\scrappers;


use Exception;

class ScrapperFactory {

    /**
     * Create a new instance of the scrapper using an ID.
     * @param $scrapperId string ID of the scrapper
     * @return BaseScrapper
     * @throws Exception
     */
    public static function createFromId($scrapperId) {
        switch (strtolower($scrapperId)) {
            case AnilistScrapper::ID:
                return new AnilistScrapper();
            case AnimeNewsNetworkScrapper::ID:
                return new AnimeNewsNetworkScrapper();
            default:
                throw new Exception("No scrapper defined for ID '$scrapperId''");
        }
    }
}