<?php

namespace mangaslib\extensions;

use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

class LinkTwigExtension extends AbstractExtension {
    public function getFilters()
    {
        return [
            new TwigFilter('linkize', [$this, 'formatValue'])
        ];
    }

    public function formatValue($value)
    {
        $value = trim(preg_replace("/[ ']/", '-', strtolower($value)));
        return $value;
    }
}