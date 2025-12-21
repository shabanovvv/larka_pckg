<?php

namespace Salavey\AiCodeReviewer\Contracts;

/**
 * Контракт для AI-клиента, который умеет делать ревью и проверять доступность сервиса.
 */
interface AiClientInterface
{
    /**
     * Выполняет запрос к AI по подготовленному промпту.
     */
    public function review(array $prompt): array;

    /**
     * Проверяет доступность AI сервиса (быстрый health-check).
     */
    public function testConnection(): bool;
}