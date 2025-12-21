<?php

namespace Salavey\AiCodeReviewer\Controllers;

use Salavey\AiCodeReviewer\Services\ReviewService;

/**
 * HTTP-контроллер для запуска AI-ревью кода (демо-страница).
 */
class AiController
{
    /**
     * Отображает демо-страницу и выполняет пример ревью.
     */
    public function index(ReviewService $reviewService): string
    {
        $response = $reviewService->review('
            class ReviewService
            {
                public function __construct(private readonly CodeReviewerInterface $codeReviewer)
                {}
                public function review(string $code, string $language): CodeReviewResultDTO
                {
                    return $this->codeReviewer->review($code, $language);
                }
                public function testConnection(): bool
                {
                    return $this->codeReviewer->testConnection();
                }
            }
        ', 'php');
        echo '<pre>';print_r($response);echo '</pre>';
        return '<h1>Hi AI</h1>';
    }
}