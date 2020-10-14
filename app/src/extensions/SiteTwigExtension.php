<?php

namespace mangaslib\extensions;

use Twig\Extension\AbstractExtension;
use Twig\Extension\GlobalsInterface;

class SiteTwigExtension extends AbstractExtension implements GlobalsInterface{
    public function getGlobals() {
        $site = ['site' => [
            'name' => "Sauleil's Mangas Library"]
        ];

        return $site;
    }
}