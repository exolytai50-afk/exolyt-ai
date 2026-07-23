<?php
/**
 * POST /api/ai_cookbook.php
 * body: { theme?, count?: 5, diet? }
 * Генерирует тематический набор рецептов
 */
declare(strict_types=1);
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/yandex.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    json_response(['error' => 'Только POST'], 405);
}

$in = json_input();
$theme = trim((string)($in['theme'] ?? ''));
$count = max(1, min(10, (int)($in['count'] ?? 5)));
$diet = trim((string)($in['diet'] ?? ''));

if (!$theme) {
    json_response(['error' => 'Укажите тему кулинарной книги'], 400);
}

$schema = 'Структура JSON: {
  "cookbook_name":"string",
  "description":"string",
  "recipes":[{
    "title":"string",
    "description":"string",
    "cuisine":"string",
    "prep_time":число,
    "cook_time":число,
    "servings":число,
    "difficulty":"string"
  }]
}';

$prompt = "Создай кулинарную книгу на тему: «$theme». "
        . "Генерируй примерно $count рецептов. "
        . ($diet ? "Тип питания: $diet. " : '')
        . 'Для каждого рецепта укажи название, краткое описание, кухню, время подготовки и готовки, количество порций и сложность.';

try {
    $data = yandex_gpt_json($prompt, $schema);

    $clean = [
        'cookbook_name' => trim((string)($data['cookbook_name'] ?? 'Кулинарная книга')),
        'description'   => trim((string)($data['description'] ?? '')),
        'recipes'       => array_values((array)($data['recipes'] ?? [])),
    ];

    json_response(['ok' => true, 'cookbook' => $clean]);
} catch (Throwable $ex) {
    json_response(['error' => APP_DEBUG ? $ex->getMessage() : 'AI временно недоступен'], 500);
}
