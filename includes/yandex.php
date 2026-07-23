<?php
/**
 * CookAI — клиент Yandex AI Studio (YandexGPT 4 Lite)
 * Константы загружаются из config/yandex_ai.php
 */
declare(strict_types=1);

function yandex_gpt_raw(array $messages, float $temperature = 0.3, int $maxTokens = 1500): string
{
    // Проверяем обязательные константы
    if (!defined('YANDEX_API_KEY') || !YANDEX_API_KEY) {
        throw new RuntimeException('YANDEX_API_KEY not configured');
    }
    if (!defined('YANDEX_FOLDER_ID') || !YANDEX_FOLDER_ID) {
        throw new RuntimeException('YANDEX_FOLDER_ID not configured');
    }
    if (!defined('YANDEX_COMPLETION_URL') || !YANDEX_COMPLETION_URL) {
        throw new RuntimeException('YANDEX_COMPLETION_URL not configured');
    }
    if (!defined('YANDEX_GPT_4_LITE_MODEL') || !YANDEX_GPT_4_LITE_MODEL) {
        throw new RuntimeException('YANDEX_GPT_4_LITE_MODEL not configured');
    }

    $body = [
        'modelUri' => 'gpt://' . YANDEX_FOLDER_ID . '/' . YANDEX_GPT_4_LITE_MODEL,
        'completionOptions' => [
            'stream'      => false,
            'temperature' => $temperature,
            'maxTokens'   => (string) $maxTokens,
        ],
        'messages' => $messages,
    ];

    $ch = curl_init(YANDEX_COMPLETION_URL);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST           => true,
        CURLOPT_TIMEOUT        => 60,
        CURLOPT_HTTPHEADER     => [
            'Content-Type: application/json',
            'Authorization: Api-Key ' . YANDEX_API_KEY,
            'x-folder-id: ' . YANDEX_FOLDER_ID,
        ],
        CURLOPT_POSTFIELDS => json_encode($body, JSON_UNESCAPED_UNICODE),
    ]);
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curlErr  = curl_error($ch);
    curl_close($ch);

    if ($response === false) {
        throw new RuntimeException('Ошибка соединения с Yandex AI: ' . $curlErr);
    }
    if ($httpCode >= 400) {
        $decoded = json_decode($response, true);
        $msg = $decoded['error']['message'] ?? $decoded['message'] ?? $response;
        throw new RuntimeException('Yandex AI вернул ошибку (' . $httpCode . '): ' . $msg);
    }

    $decoded = json_decode($response, true);
    if (!$decoded || !isset($decoded['result']['alternatives'][0]['message']['text'])) {
        throw new RuntimeException('Некорректный ответ от Yandex AI: ' . substr($response, 0, 200));
    }
    return $decoded['result']['alternatives'][0]['message']['text'];
}

function yandex_gpt_text(string $systemPrompt, array $history, float $temperature = 0.3): string
{
    $messages = [['role' => 'system', 'text' => $systemPrompt]];
    foreach ($history as $m) {
        $role = ($m['role'] ?? 'user') === 'assistant' ? 'assistant' : 'user';
        $messages[] = ['role' => $role, 'text' => (string) ($m['text'] ?? '')];
    }
    return trim(yandex_gpt_raw($messages, $temperature));
}

function extract_json(string $text): ?array
{
    $text = trim($text);
    $text = preg_replace('/^```(?:json)?\s*/i', '', $text);
    $text = preg_replace('/\s*```$/', '', $text);

    $decoded = json_decode($text, true);
    if (is_array($decoded)) return $decoded;

    if (preg_match('/\{.*\}/s', $text, $m)) {
        $decoded = json_decode($m[0], true);
        if (is_array($decoded)) return $decoded;
    }
    return null;
}

function yandex_gpt_json(string $prompt, string $schemaHint, int $retries = 1): array
{
    $system = 'Ты кулинарный AI-ассистент CookAI. Отвечай ТОЛЬКО валидным JSON без markdown, '
            . 'без пояснений и текста вне JSON. Все значения — на русском языке. ' . $schemaHint;

    $messages = [
        ['role' => 'system', 'text' => $system],
        ['role' => 'user',   'text' => $prompt],
    ];

    for ($attempt = 0; $attempt <= $retries; $attempt++) {
        $raw  = yandex_gpt_raw($messages, 0.3, 1500);
        $json = extract_json($raw);
        if ($json !== null) return $json;

        $messages[] = ['role' => 'assistant', 'text' => $raw];
        $messages[] = ['role' => 'user', 'text' => 'Верни ТОЛЬКО валидный JSON строго по схеме, без пояснений и markdown.'];
    }
    throw new RuntimeException('Модель не вернула валидный JSON после ' . ($retries + 1) . ' попыток.');
}
