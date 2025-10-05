<?php

namespace Waynelogic\FilamentCms\Service\Resize;

enum OutputFormat: string
{
    case JPG = 'jpg';
    case JPEG = 'jpeg';
    case GIF = 'gif';
    case PNG = 'png';
    case WEBP = 'webp';

    /**
     * Get the format from a file path.
     * @throws ImageException
     */
    public static function fromPath(string $path): self
    {
        $extension = strtolower(pathinfo($path, PATHINFO_EXTENSION));
        return self::tryFrom($extension) ?? throw new ImageException(
            "Invalid output format: {$extension}. Supported: jpg, gif, png, webp."
        );
    }
}
