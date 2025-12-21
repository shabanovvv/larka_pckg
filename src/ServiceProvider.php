<?php
namespace Salavey\AiCodeReviewer;

use Salavey\AiCodeReviewer\Contracts\AiClientInterface;
use Salavey\AiCodeReviewer\Contracts\CodeReviewerInterface;

/**
 * Service Provider пакета: конфиг, биндинги контейнера и публикации ресурсов.
 */
class ServiceProvider extends \Illuminate\Support\ServiceProvider
{
    /**
     * Регистрирует конфиг и зависимости пакета в контейнере.
     */
    public function register(): void
    {
        $this->mergeConfigFrom(
            __DIR__ . '/../config/ai-code-reviewer.php',
            'ai-code-reviewer'
        );

        $this->app->bind(AiClientInterface::class, function ($app) {
            $default = config('ai-code-reviewer.default');
            $config = config("ai-code-reviewer.reviewers.$default");

            return $app->make($config['client'], [
                'config' => $config,
            ]);
        });

        $this->app->bind(CodeReviewerInterface::class, function ($app) {
            $default = config('ai-code-reviewer.default');
            $config = config("ai-code-reviewer.reviewers.$default");

            return $app->make($config['driver']);
        });
    }

    /**
     * Регистрирует маршруты и публикации конфигов.
     */
    public function boot(): void
    {
        $this->loadRoutesFrom(__DIR__ . '/../routes/web.php');

        $this->publishes([
            __DIR__ . '/../config/ai-code-reviewer.php' => config_path('ai-code-reviewer.php'),
        ], 'ai-code-reviewer-config');
    }
}