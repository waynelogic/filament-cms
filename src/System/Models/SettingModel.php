<?php

namespace Waynelogic\FilamentCms\System\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class SettingModel extends Model
{
    protected $table = 'system_settings';

    protected string $code;
    protected $guarded = ['id', 'created_at', 'updated_at', 'code'];
    protected function casts(): array
    {
        return [
            'value' => 'array',
        ];
    }

    /**
     * Создает или находит экземпляр настроек по коду
     * и возвращает его как объект.
     */
    public static function instance()
    {
        $code = static::getSettingsCode();
        $cacheKey = 'settings.' . $code;

        return Cache::rememberForever($cacheKey, function () use ($code) {
            $instance = static::firstOrNew(['code' => $code]);
            if (!$instance->exists) {
                $instance->forceFill($instance->initSettingsData());
                $instance->save();
            }
            return $instance->forceFill($instance->value);
        });
    }

    public function initSettingsData(): array
    {
        return [];
    }

    /**
     * Статический метод для получения настроек по коду.
     */
    public static function get(string $key = null, $default = null) : mixed
    {
        $settings = static::instance();
        if ($key === null) {
            return $settings->value;
        }

        return $settings->value[$key] ?? $default;
    }
    public static function set(string $key, string $value = null) : bool
    {
        $settings = static::instance();
        $settings->value[$key] = $value;
        return $settings->save();
    }

    public function update(array $attributes = [], array $options = []): bool
    {
        if (! $this->exists) return false;

        return $this->forceFill($attributes)->save($options);
    }

    /**
     * Метод для сохранения настроек
     */
    public function save(array $options = []): bool
    {
        $arAttributes = $this->except([...$this->guarded, 'value']);
        $this->value = $arAttributes;
        $this->attributes['code'] = $this->code;
        foreach ($arAttributes as $key => $value) {
            unset($this->attributes[$key]);
        }

        $saved = parent::save($options);

        if ($saved) {
            // Перезаписываем кеш свежими данными
            $code = static::getSettingsCode();
            $cacheKey = 'settings.' . $code;
            Cache::forever($cacheKey, $this->forceFill($this->value));
        }

        return $saved;
    }

    /**
     * Возвращает код настроек.
     */
    protected static function getSettingsCode(): string
    {
        return (new static())->code ?? strtolower((new \ReflectionClass(static::class))->getShortName());
    }
}
