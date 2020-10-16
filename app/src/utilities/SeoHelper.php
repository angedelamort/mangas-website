<?php

namespace mangaslib\utilities;


class SeoHelper {
    /**
     * In the URL, normalize the title so we don't have space or special characters.
     * @param string $title The title you want to
     * @return string
     */
    public static function normalizeTitle($title) {
        return trim(preg_replace("/[ ']/", '-', strtolower($title)));
    }
}
