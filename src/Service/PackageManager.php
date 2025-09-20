<?php

namespace Waynelogic\FilamentCms\Service;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;
use Waynelogic\FilamentCms\Database\Traits\Singleton;

class PackageManager extends Collection
{
    use Singleton;

    /**
     * Инициализатор, вызываемый Singleton трейтом при первом создании объекта.
     */
    protected function init(): void
    {
        // Получаем имена корневых пакетов из composer.json
        $composerJson = json_decode(file_get_contents(base_path('composer.json')), true);
        $rootDevRequires = array_keys($composerJson['require-dev'] ?? []);
        $rootPackages = array_merge(array_keys($composerJson['require'] ?? []), $rootDevRequires);
        // Если composer.lock не существует, коллекция остается пустой
        $lockFile = base_path('composer.lock');
        if (! file_exists($lockFile)) {
            $this->items = []; // Устанавливаем пустые элементы для текущей коллекции
            return;
        }

        // Загружаем все пакеты из composer.lock
        $lockData = json_decode(file_get_contents($lockFile), true);
        $allLockPackages = array_merge($lockData['packages'] ?? [], $lockData['packages-dev'] ?? []);

        // Фильтруем и форматируем пакеты, используя методы коллекций,
        // и сразу же заполняем текущий объект (который является коллекцией)
        $this->items = collect($allLockPackages)
            ->whereIn('name', $rootPackages)
            ->map(fn($package) => [
                'name' => $package['name'],
                'version' => ltrim($package['version'] ?? '', 'v'),
                'description' => $package['description'] ?? '',
                'type' => in_array($package['name'], $rootDevRequires) ? 'dev' : 'prod',
            ])
            ->values() // Сбрасываем ключи массива для консистентности
            ->all();
    }

    public function checkUpdates(): self
    {
        // Готовим пул параллельных запросов к Packagist API
        $responses = Http::pool(function ($pool) {
            $requests = [];
            foreach ($this->items as $package) {
                // Для каждого пакета создаем запрос
                $requests[$package['name']] = $pool->as($package['name'])
                    ->get('https://repo.packagist.org/p2/' . $package['name'] . '.json');
            }
            return $requests;
        });

        // Используем ->transform() для модификации каждого элемента коллекции "на месте"
        $this->transform(function ($package) use ($responses) {
            $response = $responses[$package['name']] ?? null;

            if (! $response || ! $response->successful()) {
                // Если запрос не удался, помечаем как ошибку
                $package['status'] = 'error';
                return $package;
            }

            // Находим самую последнюю стабильную версию
            $latestVersionData = collect($response->json()['packages'][$package['name']])
                ->reject(fn($version) => str_contains($version['version_normalized'], 'dev')) // Игнорируем dev-версии
                ->sortByDesc('time') // Сортируем по дате релиза
                ->first();

            if (!$latestVersionData) {
                $package['status'] = 'unknown';
                return $package;
            }

            $latestVersion = ltrim($latestVersionData['version'], 'v');
            $currentVersion = $package['version'];

            // Сравниваем версии
            $comparison = version_compare($latestVersion, $currentVersion);

            // Добавляем новые данные в массив пакета
            $package['latest'] = $latestVersion;
            $package['latest_release_date'] = $latestVersionData['time'];
            $package['status'] = match ($comparison) {
                1 => 'update-available', // 1: $latestVersion > $currentVersion
                0 => 'up-to-date',       // 0: версии равны
                -1 => 'ahead',           // -1: $latestVersion < $currentVersion (например, используется dev-версия)
                default => 'unknown',
            };

            return $package;
        });

        return $this;
    }
}
