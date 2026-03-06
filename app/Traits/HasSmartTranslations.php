<?php

namespace App\Traits;

use App\Services\GeminiChatService;
use Illuminate\Support\Facades\App;

trait HasSmartTranslations
{
    /**
     * Override Spatie's getTranslation to add AI fallback.
     */
    public function getTranslation(string $attributeName, string $locale, bool $useFallbackLocale = true): mixed
    {
        $raw = $this->getAttributes()[$attributeName] ?? null;
        
        // Check if the raw value is not JSON (likely old plain text data)
        if ($raw && !str_starts_with(trim($raw), '{')) {
            // It's plain text. We should treat it as 'fr' text and convert it to JSON format for Spatie
            if ($locale === 'fr') {
                return $raw;
            }
            
            // For other locales, we translate it and save it back as JSON
            $translatedText = app(GeminiChatService::class)->translateText($raw, $locale);
            if (!empty($translatedText)) {
                $this->setTranslation($attributeName, 'fr', $raw); // Ensure FR is set
                $this->setTranslation($attributeName, $locale, $translatedText);
                $this->saveQuietly();
                return $translatedText;
            }
            return $raw; // Fallback to raw if translation fails
        }

        // Standard Spatie behavior
        $translation = $this->getSpatieTranslation($attributeName, $locale, false);

        // If translation is empty and it's not the default locale, try AI translation
        if (empty($translation) && $locale !== 'fr') {
            $baseText = $this->getSpatieTranslation($attributeName, 'fr', true);
            
            if (!empty($baseText)) {
                $translatedText = app(GeminiChatService::class)->translateText($baseText, $locale);
                
                if (!empty($translatedText) && $translatedText !== $baseText) {
                    $this->setTranslation($attributeName, $locale, $translatedText);
                    $this->saveQuietly();
                    return $translatedText;
                }
            }
        }

        return $this->getSpatieTranslation($attributeName, $locale, $useFallbackLocale);
    }
}
