<?php

namespace Salavey\AiCodeReviewer\Contracts;

use Salavey\AiCodeReviewer\DTO\CodeReviewResultDTO;

/**
 * Контракт ревьюера: анализирует код, возвращает имя и умеет проверять подключение.
 */
interface CodeReviewerInterface
{
    /**
     * Проанализировать код и вернуть результат ревью
     */
    public function review(string $code, string $language): CodeReviewResultDTO;

    /**
     * Название ревьюера (для логов и отладки)
     */
    public function getName(): string;

    /**
     * Проверить доступность AI сервиса
     */
    public function testConnection(): bool;
}