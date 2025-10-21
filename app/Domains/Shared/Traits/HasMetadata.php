<?php

declare(strict_types=1);

namespace App\Domains\Shared\Traits;

trait HasMetadata
{
    /**
     * Получить значение из метаданных
     */
    public function getMetadata(string $key, mixed $default = null): mixed
    {
        $metadataField = $this->getMetadataFieldName();
        $metadata = $this->{$metadataField} ?? [];

        return data_get($metadata, $key, $default);
    }

    /**
     * Установить значение в метаданных
     */
    public function setMetadata(string $key, mixed $value): self
    {
        $metadataField = $this->getMetadataFieldName();
        $metadata = $this->{$metadataField} ?? [];
        data_set($metadata, $key, $value);
        $this->{$metadataField} = $metadata;

        return $this;
    }

    /**
     * Проверить, существует ли ключ в метаданных
     */
    public function hasMetadata(string $key): bool
    {
        $metadataField = $this->getMetadataFieldName();
        $metadata = $this->{$metadataField} ?? [];

        return data_get($metadata, $key) !== null;
    }

    /**
     * Удалить значение из метаданных
     */
    public function forgetMetadata(string $key): self
    {
        $metadataField = $this->getMetadataFieldName();
        $metadata = $this->{$metadataField} ?? [];

        if (str_contains($key, '.')) {
            data_forget($metadata, $key);
        } else {
            unset($metadata[$key]);
        }

        $this->{$metadataField} = $metadata;

        return $this;
    }

    /**
     * Получить название поля метаданных
     * По умолчанию 'metadata', можно переопределить в модели
     */
    protected function getMetadataFieldName(): string
    {
        return property_exists($this, 'metadataField') ? $this->metadataField : 'metadata';
    }
}
