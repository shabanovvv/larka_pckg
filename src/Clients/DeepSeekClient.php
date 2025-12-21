<?php

namespace Salavey\AiCodeReviewer\Clients;

use Exception;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use InvalidArgumentException;
use Salavey\AiCodeReviewer\Contracts\AiClientInterface;
use Salavey\AiCodeReviewer\Exceptions\DeepSeekException;

/**
 * HTTP-клиент для работы с DeepSeek API (чат-комплишены).
 */
class DeepSeekClient implements AiClientInterface
{
    const DEEP_SEEK_MODEL = 'deepseek-coder';
    private string $apiKey;
    private string $baseUrl;
    private int $timeout;

    /**
     * Конфигурирует клиента из массива настроек.
     */
    public function __construct(array $config)
    {
        $this->apiKey = $config['api_key'];
        $this->baseUrl = $config['base_url'] ?? 'https://api.deepseek.com';
        $this->timeout = $config['timeout'] ?? 30;

        if (empty($this->apiKey)) {
            throw new InvalidArgumentException('DeepSeek API Key is required.');
        }
    }

    /**
     * Отправляет промпт в DeepSeek и возвращает сырой ответ.
     *
     * @throws DeepSeekException
     */
    public function review(array $prompt): array
    {
        $payload = [
            'model' => self::DEEP_SEEK_MODEL,
            'messages' => [
                ['role' => 'system', 'content' => $prompt['system']],
                ['role' => 'user', 'content' => $prompt['user']],
            ],
            'max_tokens' => 4000,
            'temperature' => 0.1,
            'stream' => false,
        ];
        try {
            Log::debug('DeepSeek Api payload', ['payload' => $payload]);
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->apiKey,
                'Content-Type' => 'application/json',
            ])
                ->timeout($this->timeout)
                ->retry(3, 100)
                ->post($this->baseUrl . '/chat/completions', $payload);
            Log::debug('DeepSeek Api response', ['response' => $response]);
            if ($response->failed()) {
                $this->handleError($response);
            }

            $data = $response->json();

            if (!isset($data['choices'][0]['message']['content'])) {
                throw new DeepSeekException('Invalid response format from DeepSeek API');
            }

            return [
                'content' => $data['choices'][0]['message']['content'],
                'usage' => $data['usage'] ?? [],
                'success' => true,
            ];
        } catch (ConnectionException $exception) {
            throw new DeepSeekException('Connection to DeepSeek API failed: ' . $exception->getMessage());
        } catch (Exception $exception) {
            throw new DeepSeekException('DeepSeek API error: ' . $exception->getMessage());
        }
    }

    /**
     * Проверка подключения
     */
    public function testConnection(): bool
    {
        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->apiKey,
            ])
                ->timeout($this->timeout)
                ->get($this->baseUrl . '/models');

            return $response->successful();
        } catch (Exception $exception) {
            return false;
        }
    }

    /**
     * Обработка ошибок API
     *
     * @throws DeepSeekException
     */
    private function handleError(Response $response): void
    {
        $statusCode = $response->status();
        $errorData = $response->json();

        $message = $errorData['error']['message'] ?? $response->body();

        throw match ($statusCode) {
            401 => new DeepSeekException('Invalid API key. Check DEEPSEEK_API_KEY in .env file.'),
            402 => new DeepSeekException('Insufficient balance. Please add funds to your DeepSeek account.'),
            404 => new DeepSeekException('API endpoint not found.'),
            429 => new DeepSeekException('Rate limit exceeded. Try again later.'),
            default => new DeepSeekException(
                "DeepSeek API error ($statusCode): " . $message,
                $statusCode
            ),
        };
    }
}