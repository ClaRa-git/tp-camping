<?php

namespace App\Twig\Extension;

use App\Twig\Runtime\NavExtensionRuntime;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;
use Twig\TwigFunction;

class NavExtension extends AbstractExtension
{
    public function getFilters(): array
    {
        return [
            new TwigFilter('number_format', [NavExtensionRuntime::class, 'numberFormat']),
            new TwigFilter('badge_user', [NavExtensionRuntime::class, 'badgeUser'], ['is_safe' => ['html']]),
         ];
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction('filters_items', [NavExtensionRuntime::class, 'filtersItems']),
        ];
    }
}
