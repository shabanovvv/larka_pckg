<?php

namespace Salavey\AiCodeReviewer\Services;

use Salavey\AiCodeReviewer\Contracts\CodeReviewerInterface;
use Salavey\AiCodeReviewer\DTO\CodeReviewResultDTO;

/**
 * Сервис-обёртка над конкретным CodeReviewer: точка входа для ревью и проверки подключения.
 */
class ReviewService
{
    /**
     * Создаёт сервис, используя выбранный CodeReviewer из контейнера.
     */
    public function __construct(private readonly CodeReviewerInterface $codeReviewer)
    {}

    /**
     * Делегирует ревью кода выбранному ревьюеру.
     */
    public function review(string $code, string $language): CodeReviewResultDTO
    {
        return $this->codeReviewer->review($code, $language);
    }

    /**
     * Делегирует проверку доступности выбранному ревьюеру.
     */
    public function testConnection(): bool
    {
        return $this->codeReviewer->testConnection();
    }
}