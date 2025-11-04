<?php

declare(strict_types=1);

namespace Tests\Integration\Auth\Infrastructure;

use Tests\TestCase;
use Illuminate\Support\Facades\Lang;

class TranslationTest extends TestCase
{
    public function test_required_translations_exist()
    {
        // Desactivar temporalmente el fallback
        $originalFallback = config('app.fallback_locale');
        config(['app.fallback_locale' => null]);

        $requiredTranslations = [
            'messages.user.registered_success',
            'messages.user.email_already_exists'
        ];

        foreach ($requiredTranslations as $key) {
            $translationExists = Lang::has($key);
            $translation = Lang::get($key);
            $isUsingFallback = $translation === $key;

            $this->assertTrue(
                $translationExists && !$isUsingFallback,
                "Translation missing or using fallback for key: {$key} in locale: " . app()->getLocale()
            );
        }

        // Restaurar el fallback
        config(['app.fallback_locale' => $originalFallback]);
    }
}