<?php

namespace Waynelogic\FilamentCms\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\File;
use Waynelogic\FilamentCms\Service\Resize\ResizeMode;
use Waynelogic\FilamentCms\Service\Resize\Resizer;

class ImageResizerController extends Controller
{
    public function show($path, Request $request)
    {
        $appUrl = rtrim(config('app.url'), '/');
        if (str_starts_with($path, $appUrl)) {
            $relativePath = substr($path, strlen($appUrl));
            // Убираем возможный начальный слэш, чтобы получить путь относительно public/
            $path = ltrim($relativePath, '/');
        }

        // 1. Получение пути к оригинальному изображению
        $originalPath = public_path($path);
        if (!File::exists($originalPath)) {
            abort(404, 'Image not found');
        }

        // 2. Валидация и получение параметров из запроса.
        // Приводим к целым числам для безопасности и устанавливаем значения по умолчанию.
        $params = [
            'w' => (int) $request->input('w'),
            'h' => (int) $request->input('h'),
            'm' => (string) $request->input('m', 'crop'),
            'q' => (int) $request->input('q', 90), // Качество по умолчанию 90
        ];

        // 3. Определяем формат. Если не указан, используем формат оригинала.
        $format = $request->input('f', strtolower(pathinfo($path, PATHINFO_EXTENSION)));
        $params['f'] = $format;

        // 4. Генерируем оптимизированный путь для кеша
        $cacheKey = md5($path . http_build_query($params));
        $subDir = substr($cacheKey, 0, 2); // например: 'a3'
        $cachePath = "cache/images/{$subDir}/{$cacheKey}.{$format}";
        $cachedImagePath = Storage::path($cachePath);
        $cacheDirectory = dirname($cachedImagePath);

        if (File::exists($cachedImagePath)) {
            if (File::lastModified($originalPath) > File::lastModified($cachedImagePath)) {
                File::delete($cachedImagePath);
            } else {
                return response()->file($cachedImagePath);
            }
        }

        try {
            if (!File::isDirectory($cacheDirectory)) {
                File::makeDirectory($cacheDirectory, 0755, true);
            }
            Resizer::open($originalPath)
                ->resize($params['w'], $params['h'], ResizeMode::from($params['m']))
                ->save($cachedImagePath, 85);
        } catch (\Exception $e) {
            // В случае ошибки при обработке изображения, возвращаем 500 ошибку
            report($e); // Логируем ошибку
            abort(500, 'Error processing image');
        }

        return response()->file($cachedImagePath);
    }
}
