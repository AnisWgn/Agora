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
        // Si l'URL est absolue, la retourner telle quelle
        if (preg_match('#^(https?:)?//#', $path)) {
            return $path;
        }
        // Assurer que le chemin commence par un slash
        return '/' . ltrim($path, '/');
    }
}

