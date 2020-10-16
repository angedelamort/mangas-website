<?php

namespace mangaslib\extensions;

use mangaslib\utilities\SeoHelper;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

class LinkTwigExtension extends AbstractExtension {
    public function getFilters() {
        return [
            new TwigFilter('normalize', [$this, 'formatValue'])
        ];
    }

    public function formatValue($value) {
        return SeoHelper::normalizeTitle($value);
    }
}