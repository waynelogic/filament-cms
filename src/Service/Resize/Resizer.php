<?php

namespace Waynelogic\FilamentCms\Service\Resize;

use Symfony\Component\HttpFoundation\File\File as FileObj;
use GdImage;

class Resizer
{
    private GdImage $image;
    public readonly GdImage $originalImage;
    public readonly int $originalWidth;
    public readonly int $originalHeight;

    public readonly string $mime;
    public readonly ?int $orientation;

    /**
     * @param FileObj $file The image file object.
     * @throws ImageException
     */
    private function __construct(public readonly FileObj $file)
    {
        if (!extension_loaded('gd')) {
            throw new ImageException('GD PHP extension is required.');
        }

        $this->mime = $file->getMimeType();
        $this->originalImage = $this->openImage($file->getPathname());
        $this->image = $this->originalImage;

        $this->orientation = $this->readOrientation($file->getPathname());
        list($this->originalWidth, $this->originalHeight) = $this->getOrientedDimensions();
    }

    /**
     * Static constructor.
     * @param string|FileObj $file Path to image or a FileObj instance.
     */
    public static function open(string|FileObj $file): self
    {
        if (is_string($file)) {
            $file = new FileObj($file);
        }
        return new self($file);
    }

    /**
     * Resizes and/or crops an image.
     *
     * @param int        $newWidth  The target width.
     * @param int        $newHeight The target height.
     * @param ResizeMode $mode      The resizing mode.
     * @param int[]      $offset    For CROP mode: [x, y] offset from center.
     */
    public function resize(int $newWidth, int $newHeight, ResizeMode $mode = ResizeMode::AUTO, array $offset = [0, 0]): self
    {
        if ($newWidth <= 0 && $newHeight <= 0) {
            return $this; // No operation needed
        }
        if ($newWidth <= 0) {
            $newWidth = (int)($this->originalWidth / $this->originalHeight * $newHeight);
        }
        if ($newHeight <= 0) {
            $newHeight = (int)($this->originalHeight / $this->originalWidth * $newWidth);
        }

        $rotatedOriginal = $this->getRotatedOriginal();

        // Optimized crop logic: calculate source coordinates and resize in one step
        if ($mode === ResizeMode::CROP) {
            $src_x = 0;
            $src_y = 0;
            $src_w = $this->originalWidth;
            $src_h = $this->originalHeight;

            $ratio = max($newWidth / $this->originalWidth, $newHeight / $this->originalHeight);
            $src_w = (int)($newWidth / $ratio);
            $src_h = (int)($newHeight / $ratio);
            $src_x = (int)(($this->originalWidth - $src_w) / 2) - $offset[0];
            $src_y = (int)(($this->originalHeight - $src_h) / 2) - $offset[1];

            $newImage = imagecreatetruecolor($newWidth, $newHeight);
            $this->preserveTransparency($newImage);
            imagecopyresampled($newImage, $rotatedOriginal, 0, 0, $src_x, $src_y, $newWidth, $newHeight, $src_w, $src_h);
        } else {
            // Logic for other modes
            list($optimalWidth, $optimalHeight) = $this->calculateDimensions($newWidth, $newHeight, $mode);

            if ($this->mime === 'image/gif') {
                $newImage = imagescale($rotatedOriginal, $optimalWidth, $optimalHeight, IMG_NEAREST_NEIGHBOUR);
            } else {
                $newImage = imagecreatetruecolor($optimalWidth, $optimalHeight);
                $this->preserveTransparency($newImage);
                imagecopyresampled($newImage, $rotatedOriginal, 0, 0, 0, 0, $optimalWidth, $optimalHeight, $this->originalWidth, $this->originalHeight);
            }
        }

        imagedestroy($this->image);
        if ($this->image !== $this->originalImage) {
            imagedestroy($rotatedOriginal);
        }

        $this->image = $newImage;
        return $this;
    }

    /**
     * Save the processed image to a file.
     *
     * @param string $savePath The destination path.
     * @param int    $quality  Image quality (0-100).
     * @param bool   $interlace Enable interlacing.
     * @throws ImageException
     */
    public function save(string $savePath, int $quality = 90, bool $interlace = false): bool
    {
        $quality = max(0, min(100, $quality));
        $format = OutputFormat::fromPath($savePath);

        if ($interlace) {
            imageinterlace($this->image, true);
        }

        $result = match ($format) {
            OutputFormat::JPEG, OutputFormat::JPG => $this->saveJpg($savePath, $quality),
            OutputFormat::GIF => imagegif($this->image, $savePath),
            OutputFormat::PNG => imagepng($this->image, $savePath, 9 - (int)round(($quality / 100) * 9)),
            OutputFormat::WEBP => imagewebp($this->image, $savePath, $quality),
        };

        if (!$result) {
            throw new ImageException("Failed to save image to {$savePath}.");
        }

        imagedestroy($this->image);

        return true;
    }

    /**
     * Handles saving JPEG by placing transparent images on a white background.
     */
    private function saveJpg(string $savePath, int $quality): bool
    {
        $width = imagesx($this->image);
        $height = imagesy($this->image);
        $canvas = imagecreatetruecolor($width, $height);

        $white = imagecolorallocate($canvas, 255, 255, 255);
        imagefill($canvas, 0, 0, $white);
        imagecopy($canvas, $this->image, 0, 0, 0, 0, $width, $height);

        $result = imagejpeg($canvas, $savePath, $quality);
        imagedestroy($canvas);

        return $result;
    }


    /**
     * Calculate optimal dimensions based on mode.
     */
    private function calculateDimensions(int $newWidth, int $newHeight, ResizeMode $mode): array
    {
        return match ($mode) {
            ResizeMode::EXACT => [$newWidth, $newHeight],
            ResizeMode::LANDSCAPE => [$newWidth, (int)($this->originalHeight / $this->originalWidth * $newWidth)],
            ResizeMode::PORTRAIT => [(int)($this->originalWidth / $this->originalHeight * $newHeight), $newHeight],
            ResizeMode::AUTO => $this->getDimensionsByAuto($newWidth, $newHeight),
            ResizeMode::FIT => $this->getDimensionsByFit($newWidth, $newHeight),
            // CROP is handled separately
            default => [$this->originalWidth, $this->originalHeight],
        };
    }

    private function getDimensionsByAuto(int $newWidth, int $newHeight): array
    {
        if ($this->originalHeight < $this->originalWidth) { // Landscape
            return [$newWidth, (int)($this->originalHeight / $this->originalWidth * $newWidth)];
        }
        if ($this->originalHeight > $this->originalWidth) { // Portrait
            return [(int)($this->originalWidth / $this->originalHeight * $newHeight), $newHeight];
        }
        // Square
        return ($newHeight < $newWidth)
            ? [$newWidth, (int)($this->originalHeight / $this->originalWidth * $newWidth)]
            : [(int)($this->originalWidth / $this->originalHeight * $newHeight), $newHeight];
    }

    private function getDimensionsByFit(int $maxWidth, int $maxHeight): array
    {
        $ratio = min($maxWidth / $this->originalWidth, $maxHeight / $this->originalHeight);
        return [(int)($this->originalWidth * $ratio), (int)($this->originalHeight * $ratio)];
    }

    /**
     * Opens an image from a path.
     * @throws ImageException
     */
    private function openImage(string $path): GdImage
    {
        $image = match ($this->mime) {
            'image/jpeg' => @imagecreatefromjpeg($path),
            'image/gif'  => @imagecreatefromgif($path),
            'image/png'  => @imagecreatefrompng($path),
            'image/webp' => @imagecreatefromwebp($path),
            default      => throw new ImageException("Invalid mime type: {$this->mime}."),
        };

        if ($image === false) {
            throw new ImageException("Failed to open image file at {$path}.");
        }

        $this->preserveTransparency($image);
        return $image;
    }

    /**
     * Reads EXIF orientation data from a JPEG file.
     */
    private function readOrientation(string $path): ?int
    {
        if ($this->mime !== 'image/jpeg' || !function_exists('exif_read_data')) {
            return null;
        }
        $exif = @exif_read_data($path);
        return $exif['Orientation'] ?? null;
    }

    /**
     * Gets image dimensions, corrected for EXIF orientation.
     */
    private function getOrientedDimensions(): array
    {
        $width = imagesx($this->originalImage);
        $height = imagesy($this->originalImage);

        return in_array($this->orientation, [5, 6, 7, 8])
            ? [$height, $width]
            : [$width, $height];
    }

    /**
     * Returns the original image, rotated according to EXIF data.
     */
    private function getRotatedOriginal(): GdImage
    {
        $angle = match ($this->orientation) {
            3, 4 => 180.0,
            5, 6 => -90.0, // 270
            7, 8 => 90.0,
            default => 0.0,
        };

        return ($angle !== 0.0)
            ? imagerotate($this->originalImage, $angle, 0)
            : $this->originalImage;
    }

    /**
     * Configures a GdImage resource to preserve transparency for PNG, GIF, and WebP.
     */
    private function preserveTransparency(GdImage $image): void
    {
        match ($this->mime) {
            'image/gif' => (function() use ($image) {
                $transparentIndex = imagecolortransparent($image);
                if ($transparentIndex >= 0) {
                    $color = imagecolorsforindex($image, $transparentIndex);
                    $alphaIndex = imagecolorallocatealpha($image, $color['red'], $color['green'], $color['blue'], 127);
                    imagefill($image, 0, 0, $alphaIndex);
                    imagecolortransparent($image, $alphaIndex);
                }
            })(),
            'image/png', 'image/webp' => (function() use ($image) {
                imagealphablending($image, false);
                imagesavealpha($image, true);
            })(),
            default => null,
        };
    }
}
