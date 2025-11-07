<?php

namespace App\Twig;

use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class AppExtension extends AbstractExtension
{
    public function getFunctions(): array
    {
        return [
            new TwigFunction('asset', [$this, 'getAssetUrl']),
        ];
    }

    public function getAssetUrl(string $path): string
    {
        if (preg_match('#^(https?:)?//#', $path)) {
            return $path;
        }
        return '/' . ltrim($path, '/');
    }
}

