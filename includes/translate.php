<?php
require_once __DIR__ . '/config.php';

/**
 * Translate text via MyMemory. Returns null on any failure so callers can
 * fall back to leaving the target field untouched rather than erroring out.
 * Text over MyMemory's ~500 character request limit is split on paragraph/
 * sentence boundaries and translated in chunks, then rejoined.
 */
function translate_text(string $text, string $sourceLang = 'he', string $targetLang = 'en'): ?string
{
    $text = trim($text);
    if ($text === '') {
        return '';
    }

    $maxChars = 450;
    if (mb_strlen($text, 'UTF-8') <= $maxChars) {
        return translate_chunk($text, $sourceLang, $targetLang);
    }

    $translated = [];
    foreach (translate_split_chunks($text, $maxChars) as $chunk) {
        $result = translate_chunk($chunk, $sourceLang, $targetLang);
        if ($result === null) {
            return null;
        }
        $translated[] = $result;
    }
    return implode('', $translated);
}

function translate_chunk(string $text, string $sourceLang, string $targetLang): ?string
{
    $query = http_build_query([
        'q' => $text,
        'langpair' => $sourceLang . '|' . $targetLang,
        'de' => TRANSLATE_EMAIL,
    ]);

    $ch = curl_init(TRANSLATE_API_URL . '?' . $query);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT => 15,
        CURLOPT_CONNECTTIMEOUT => 5,
    ]);
    $response = curl_exec($ch);
    $error = curl_error($ch);
    $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);

    if ($response === false || $error !== '' || $status < 200 || $status >= 300) {
        return null;
    }

    $data = json_decode((string) $response, true);
    if (($data['responseStatus'] ?? null) != 200) {
        return null;
    }
    $translated = $data['responseData']['translatedText'] ?? null;
    if ($translated === null || str_starts_with($translated, 'MYMEMORY WARNING')) {
        return null;
    }
    return $translated;
}

/**
 * Break text into pieces at or under $maxChars, preferring to split on
 * paragraph (</p>) and then sentence boundaries so translation quality
 * isn't hurt by cutting mid-sentence.
 */
function translate_split_chunks(string $text, int $maxChars): array
{
    $paragraphs = preg_split('/(?<=<\/p>)/u', $text, -1, PREG_SPLIT_NO_EMPTY) ?: [$text];
    $chunks = [];

    foreach ($paragraphs as $paragraph) {
        if (mb_strlen($paragraph, 'UTF-8') <= $maxChars) {
            $chunks[] = $paragraph;
            continue;
        }

        $sentences = preg_split('/(?<=[.!?׃])\s+/u', $paragraph, -1, PREG_SPLIT_NO_EMPTY) ?: [$paragraph];
        $buffer = '';
        foreach ($sentences as $sentence) {
            if ($buffer !== '' && mb_strlen($buffer . ' ' . $sentence, 'UTF-8') > $maxChars) {
                $chunks[] = $buffer;
                $buffer = $sentence;
            } else {
                $buffer = $buffer === '' ? $sentence : $buffer . ' ' . $sentence;
            }
        }
        if ($buffer !== '') {
            $chunks[] = $buffer;
        }
    }

    return $chunks;
}
