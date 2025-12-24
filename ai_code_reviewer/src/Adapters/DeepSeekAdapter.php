<?php

namespace Salavey\AiCodeReviewer\Adapters;

use Salavey\AiCodeReviewer\Clients\DeepSeekClient;
use Salavey\AiCodeReviewer\Contracts\AiClientInterface;
use Salavey\AiCodeReviewer\Contracts\CodeReviewerInterface;
use Salavey\AiCodeReviewer\DTO\CodeReviewResultDTO;
use Salavey\AiCodeReviewer\Prompts\PromptBuilder;

/**
 * Адаптер DeepSeek: связывает PromptBuilder и AI-клиент, возвращая DTO результата ревью.
 */
class DeepSeekAdapter implements CodeReviewerInterface
{
    /**
     * Создаёт адаптер с внедрёнными клиентом и билдером промптов.
     */
    public function __construct(
        private readonly AiClientInterface $client,
        private readonly PromptBuilder $promptBuilder
    ) {
    }

    /**
     * Выполняет ревью кода и возвращает структурированный результат.
     */
    public function review(string $code, string $language): CodeReviewResultDTO
    {
        $prompt = $this->promptBuilder->buildForReview($code, $language);
        $response = $this->client->review($prompt);

        return $this->parseResponse($response['content'], $code, $language);
    }

    /**
     * Возвращает имя текущего ревьюера/провайдера.
     */
    public function getName(): string
    {
        return 'DeepSeek';
    }

    /**
     * Проверяет доступность DeepSeek API.
     */
    public function testConnection(): bool
    {
        return $this->client->testConnection();
    }

    /**
     * Парсит JSON ответ от DeepSeek в DTO
     */
    private function parseResponse(string $jsonResponse, string $originalCode, string $language): CodeReviewResultDTO
    {
        $data = json_decode($jsonResponse, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            // Если не JSON, создаем DTO с ошибкой
            return new CodeReviewResultDTO(
                score: 0,
                issues: [['type' => 'parse_error', 'severity' => 'high', 'message' => 'Не удалось распарсить ответ AI']],
                suggestions: [],
                summary: 'Ошибка анализа: неверный формат ответа',
                originalCode: $originalCode,
                language: $language,
                metadata: ['parse_error' => true, 'raw_response' => substr($jsonResponse, 0, 500)]
            );
        }

        return CodeReviewResultDTO::fromArray(array_merge($data, [
            'originalCode' => $originalCode,
            'language' => $language,
        ]));
    }
}