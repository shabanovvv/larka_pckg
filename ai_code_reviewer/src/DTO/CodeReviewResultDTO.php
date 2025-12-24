<?php

namespace Salavey\AiCodeReviewer\DTO;

/**
 * DTO результата ревью: оценка, проблемы, рекомендации и метаданные.
 */
class CodeReviewResultDTO
{
    /**
     * Создаёт DTO результата ревью.
     */
    public function __construct(
        public int $score,
        public array $issues,
        public array $suggestions,
        public string $summary,
        public string $originalCode,
        public string $language,
        public array $metadata = []
    ) {}

    /**
     * Создаёт DTO из массива (например, из распарсенного JSON).
     */
    public static function fromArray(array $data): self
    {
        return new self(
            score: $data['score'] ?? 0,
            issues: $data['issues'] ?? [],
            suggestions: $data['suggestions'] ?? [],
            summary: $data['summary'] ?? '',
            originalCode: $data['originalCode'] ?? '',
            language: $data['language'] ?? 'php',
            metadata: $data['metadata'] ?? []
        );
    }
}