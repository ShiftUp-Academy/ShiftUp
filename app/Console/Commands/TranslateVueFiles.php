<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Gemini\Laravel\Facades\Gemini;
use Illuminate\Support\Facades\Log;

class TranslateVueFiles extends Command
{
    protected $signature = 'translate:vue 
        {--file= : Translate a specific Vue file (relative to resources/js)}
        {--dir= : Translate all Vue files in a directory}
        {--langs=en,mg : Comma-separated language codes to translate to}
        {--all : Translate all strings in fr.json that are missing in other languages}
        {--dry-run : Show what would be extracted without modifying files}';

    protected $description = 'Extract French strings from Vue files, replace with $t() and generate translations via Gemini';

    private array $extractedStrings = [];
    private array $frTranslations;
    private array $enTranslations;
    private array $mgTranslations;

    public function handle()
    {
        $this->frTranslations = $this->loadJson('fr');
        $this->enTranslations = $this->loadJson('en');
        $this->mgTranslations = $this->loadJson('mg');

        $files = $this->getTargetFiles();

        if (empty($files)) {
            $this->error('No Vue files found.');
            return 1;
        }

        $this->info("Found " . count($files) . " Vue file(s) to process...");

        foreach ($files as $filePath) {
            $this->processFile($filePath);
        }

        if ($this->option('all')) {
            $this->extractedStrings = $this->frTranslations;
        }

        if (empty($this->extractedStrings) && !$this->option('all')) {
            $this->info('No new strings to translate. Use --all to process all existing strings.');
            return 0;
        }

        if (!$this->option('all')) {
            $this->info("\n📝 Found " . count($this->extractedStrings) . " new strings to translate:");
            foreach ($this->extractedStrings as $key => $val) {
                $this->line("  [$key] => $val");
            }
        } else {
            $this->info("\n📝 Processing all " . count($this->extractedStrings) . " existing strings...");
        }

        if ($this->option('dry-run')) {
            $this->warn("\n[Dry Run] No files modified.");
            return 0;
        }

        $langs = explode(',', $this->option('langs'));

        foreach ($langs as $lang) {
            $lang = trim($lang);
            $this->info("\n🌍 Translating to [$lang]...");
            $this->translateAndSave($lang);
        }

        // Also save FR (source) if we extracted new ones
        if (!$this->option('all')) {
            foreach ($this->extractedStrings as $key => $value) {
                $this->frTranslations[$key] = $value;
            }
            $this->saveJson('fr', $this->frTranslations);
        }
        $this->info("✅ All translations saved!");

        return 0;
    }

    private function getTargetFiles(): array
    {
        $base = resource_path('js');

        if ($file = $this->option('file')) {
            $full = $base . '/' . ltrim($file, '/');
            return file_exists($full) ? [$full] : [];
        }

        if ($dir = $this->option('dir')) {
            $fullDir = $base . '/' . ltrim($dir, '/');
            return $this->getVueFiles($fullDir);
        }

        // Default: all public-facing pages and sections
        return array_merge(
            $this->getVueFiles(resource_path('js/vue/pages')),
            $this->getVueFiles(resource_path('js/vue/components/sections'))
        );
    }

    private function getVueFiles(string $dir): array
    {
        if (!is_dir($dir)) return [];
        $files = [];
        foreach (new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($dir)) as $file) {
            if ($file->getExtension() === 'vue' && !str_contains($file->getPath(), 'PagesAdmin')) {
                $files[] = $file->getPathname();
            }
        }
        return $files;
    }

    private function processFile(string $filePath): void
    {
        $relativePath = str_replace(resource_path('js') . DIRECTORY_SEPARATOR, '', $filePath);
        $this->line("\n📄 Processing: $relativePath");

        $content = file_get_contents($filePath);
        $originalContent = $content;

        // Extract template section only
        if (!preg_match('/<template>(.*?)<\/template>/s', $content, $templateMatch)) {
            $this->warn("  No <template> found, skipping.");
            return;
        }

        $template = $templateMatch[1];
        $newTemplate = $this->replaceStringsInTemplate($template, $filePath);

        if ($newTemplate !== $template) {
            $content = str_replace($templateMatch[0], "<template>$newTemplate</template>", $content);

            if (!$this->option('dry-run')) {
                file_put_contents($filePath, $content);
                $this->info("  ✏️  File updated.");
            }
        } else {
            $this->line("  ✔️  No changes needed.");
        }
    }

    private function replaceStringsInTemplate(string $template, string $filePath): string
    {
        // Match text content that is French (not already translated, not inside {{ }}, not in attributes)
        // Pattern: content between tags that is French text
        $pattern = '/(?<=>)([^<>{}]+?)(?=<)/';
        
        $template = preg_replace_callback($pattern, function ($matches) use ($filePath) {
            $text = $matches[1];
            $trimmed = trim($text);
            
            // Skip if empty, numeric, special chars only, or already uses $t
            if (empty($trimmed) || strlen($trimmed) < 3) return $matches[0];
            if (preg_match('/^[\s\d\.\,\:\;\-\!\?\&\@\#\/\\\\\|_\+\=\*]+$/', $trimmed)) return $matches[0];
            if (strpos($trimmed, '$t(') !== false) return $matches[0];
            if (strpos($trimmed, '{{') !== false) return $matches[0];
            if (str_starts_with($trimmed, '©') || str_starts_with($trimmed, 'http')) return $matches[0];
            
            // Generate a key from the text
            $key = $this->generateKey($trimmed, $filePath);
            
            // Check if already in FR translations
            $existing = array_search($trimmed, $this->frTranslations);
            if ($existing !== false) {
                $key = $existing;
            } else {
                $this->extractedStrings[$key] = $trimmed;
                $this->frTranslations[$key] = $trimmed;
            }
            
            $leading = substr($text, 0, strlen($text) - strlen(ltrim($text)));
            $trailing = substr($text, strlen(rtrim($text)));
            
            return $leading . "{{ \$t('$key') }}" . $trailing;
        }, $template);

        $attributes = ['placeholder', 'title', 'description', 'cursorText', 'label', 'header'];
        foreach ($attributes as $attr) {
            $template = preg_replace_callback(
                '/\b' . $attr . '="([^"]{3,})"/i',
                function ($m) use ($filePath, $attr) {
                    $text = $m[1];
                    if (strpos($text, '$t') !== false) return $m[0];
                    if (strpos($text, '{{') !== false) return $m[0];
                    if (!preg_match('/[a-zA-ZÀ-ÿ]{2,}/', $text)) return $m[0];
                    
                    if (in_array($text, ['true', 'false', 'null'])) return $m[0];
                    
                    $key = $this->generateKey($text, $filePath);
                    $existing = array_search($text, $this->frTranslations);
                    if ($existing !== false) {
                        $key = $existing;
                    } else {
                        $this->extractedStrings[$key] = $text;
                        $this->frTranslations[$key] = $text;
                    }
                    return ':' . $attr . '="$t(\'' . $key . '\')"';
                },
                $template
            );
        }

        return $template;
    }

    private function generateKey(string $text, string $filePath): string
    {
        // Get page/component name from file path
        $filename = pathinfo($filePath, PATHINFO_FILENAME);
        
        // Create a slug from first few words
        $words = preg_split('/\s+/', $text, 5);
        $slug = implode('_', array_slice($words, 0, 3));
        $slug = preg_replace('/[^a-zA-Z0-9_]/', '', $slug);
        $slug = trim($slug, '_');
        
        // Make unique key
        $key = $filename . '.' . strtolower(substr($slug, 0, 40));
        
        // Ensure uniqueness
        $base = $key;
        $i = 2;
        while (isset($this->frTranslations[$key]) && $this->frTranslations[$key] !== $text) {
            $key = $base . '_' . $i++;
        }

        return $key;
    }

    private function translateAndSave(string $lang): void
    {
        $existing = $this->loadJson($lang);
        $toTranslate = [];

        foreach ($this->extractedStrings as $key => $frText) {
            $existingValue = $existing[$key] ?? null;
            
            // Translate if missing OR if it's the same as French (and long enough to likely need translation)
            if ($existingValue === null || ($existingValue === $frText && strlen($frText) > 4)) {
                $toTranslate[$key] = $frText;
            }
        }

        if (empty($toTranslate)) {
            $this->line("  All strings already translated for [$lang].");
            return;
        }

        // Batch translate for efficiency
        $langNames = ['en' => 'English', 'mg' => 'Malagasy (Madagascar)'];
        $langName = $langNames[$lang] ?? $lang;

        $chunks = array_chunk($toTranslate, 20, true);
        $translated = $existing;

        foreach ($chunks as $i => $chunk) {
            $this->line("  Translating batch " . ($i + 1) . "/" . count($chunks) . "...");

            $jsonInput = json_encode($chunk, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);

            $prompt = "You are a professional expert translator.
Translate the following JSON VALUES from French to $langName.
Keep the exactly SAME KEYS.
Translate ALL values, even short ones.
DO NOT use French in the output values.
Output MUST be ONLY valid JSON.

JSON SOURCE (French):
$jsonInput";

            try {
                $result = Gemini::generativeModel('gemini-2.5-flash')->generateContent($prompt);
                $responseText = trim($result->text());
                
                // Debug log
                Log::info("Gemini translation batch for $lang", [
                    'input' => $chunk,
                    'output' => $responseText
                ]);
                $this->line("  Gemini responded (" . strlen($responseText) . " chars)");

                // Strip markdown code blocks if present
                $responseText = preg_replace('/^```json\s*/m', '', $responseText);
                $responseText = preg_replace('/^```\s*/m', '', $responseText);
                $responseText = preg_replace('/```$/m', '', $responseText);
                $responseText = trim($responseText);

                $decoded = json_decode($responseText, true);
                if (is_array($decoded)) {
                    foreach ($decoded as $key => $value) {
                        $translated[$key] = $value;
                    }
                    $this->info("  ✅ Batch translated (" . count($decoded) . " strings)");
                } else {
                    $this->warn("  ⚠️  Could not parse Gemini response for batch.");
                    $this->line("Response: " . substr($responseText, 0, 100) . "...");
                    foreach ($chunk as $k => $v) {
                        if (!isset($translated[$k])) $translated[$k] = $v;
                    }
                }
            } catch (\Exception $e) {
                $this->error("  ❌ Gemini error: " . $e->getMessage());
                foreach ($chunk as $k => $v) {
                    if (!isset($translated[$k])) $translated[$k] = $v;
                }
            }

            // Small delay to avoid rate limiting
            if ($i < count($chunks) - 1) sleep(1);
        }

        $this->saveJson($lang, $translated);
    }

    private function loadJson(string $lang): array
    {
        $path = lang_path("$lang.json");
        if (!file_exists($path)) return [];
        $data = json_decode(file_get_contents($path), true);
        return is_array($data) ? $data : [];
    }

    private function saveJson(string $lang, array $data): void
    {
        $path = lang_path("$lang.json");
        ksort($data);
        file_put_contents($path, json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
        $this->info("  💾 Saved $lang.json (" . count($data) . " strings)");
    }
}
