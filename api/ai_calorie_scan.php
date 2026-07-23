<?php
/**
 * POST /api/ai_calorie_scan.php
 * body: { image?, description?, save?: bool }
 * 
 * Двухступенчатый процесс:
 * 1. Qwen3.6-35B анализирует фото и выдаёт данные о блюде
 * 2. YandexGPT 4 Lite использует результат и рассчитывает калории
 */
declare(strict_types=1);
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/yandex.php';
require_once __DIR__ . '/../includes/yandex_vision.php';
require_once __DIR__ . '/../includes/ai_guard.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') json_response(['error' => 'Только POST'], 405);
ai_guard('calorie');

$in    = json_input();
$image = (string)($in['image'] ?? '');
$desc  = trim((string)($in['description'] ?? ''));
$save  = !empty($in['save']);

if ($image === '' && $desc === '') {
    json_response(['error' => 'Загрузите фото или опишите блюдо'], 400);
}

// Схема для VLM (Qwen3.6-35B) - распознавание блюда
$vlm_schema = 'Структура JSON: {
  "dish_name":"string",
  "ingredients":["ингредиент 1","ингредиент 2"],
  "estimated_weight_g":число,
  "notes":"краткие примечания"
}';

// Схема для LLM (YandexGPT 4 Lite) - расчёт калорий
$llm_schema = 'Структура JSON: {
  "calories":число,
  "proteins":число,
  "fats":число,
  "carbs":число,
  "portion":"string",
  "confidence":"низкая|средняя|высокая",
  "note":"string"
}';

try {
    // ЭТАП 1: Анализ фото через VLM (Qwen3.6-35B)
    $vlm_data = null;
    if ($image !== '') {
        $base64 = normalize_image_data_url($image);
        $vlm_prompt = 'Ты — ассистент по питанию. Посмотри на фото еды и ответь строго в формате JSON без лишних слов. '
                    . 'Не придумывай ингредиенты, которых нет на фото. Если не уверен в названии — выбери наиболее вероятный вариант.'
                    . ($desc !== '' ? " Дополнительный контекст: «{$desc}»." : '');
        $vlm_data = yandex_vision_json($base64, $vlm_prompt, $vlm_schema);
        $source = 'photo';
    } else {
        // Если только текстовое описание, используем LLM напрямую
        $source = 'text';
    }

    // ЭТАП 2: Расчёт калорий через LLM (YandexGPT 4 Lite)
    if ($vlm_data !== null) {
        // Построение промпта на основе данных от VLM
        $dish_name = $vlm_data['dish_name'] ?? 'Блюдо';
        $ingredients_list = implode(', ', (array)($vlm_data['ingredients'] ?? []));
        $weight = $vlm_data['estimated_weight_g'] ?? '350';
        $notes = $vlm_data['notes'] ?? '';

        $llm_prompt = "Рассчитай пищевую ценность блюда на основе этих данных:\n"
                    . "Название: $dish_name\n"
                    . "Ингредиенты: $ingredients_list\n"
                    . "Приблизительный вес: {$weight}г\n"
                    . "Примечания: $notes\n"
                    . "Дай калорийность и БЖУ на порцию ({$weight}г).";
    } else {
        // Текстовое описание напрямую
        $llm_prompt = "Оцени пищевую ценность блюда по описанию: «{$desc}». "
                    . 'Дай калорийность и БЖУ на порцию (примерно 350г), укажи наиболее вероятный вес.';
    }

    $llm_data = yandex_gpt_json($llm_prompt, $llm_schema);

    // Сборка финального результата
    $result = [
        'dish'        => trim((string)($vlm_data['dish_name'] ?? $desc ?? 'Блюдо')),
        'portion'     => trim((string)($llm_data['portion'] ?? '1 порция')),
        'calories'    => max(0, (int)($llm_data['calories'] ?? 0)),
        'proteins'    => max(0, (int)($llm_data['proteins'] ?? 0)),
        'fats'        => max(0, (int)($llm_data['fats'] ?? 0)),
        'carbs'       => max(0, (int)($llm_data['carbs'] ?? 0)),
        'ingredients' => array_values(array_filter(array_map('strval', (array)($vlm_data['ingredients'] ?? [])))),
        'confidence'  => (string)($llm_data['confidence'] ?? 'средняя'),
        'note'        => trim((string)($llm_data['note'] ?? '')),
    ];

    $scanId = null;
    if ($save && is_logged_in()) {
        $scanId = db_insert(
            'INSERT INTO calorie_scans
             (user_email, dish, portion, calories, proteins, fats, carbs, source, confidence, created_at)
             VALUES (?,?,?,?,?,?,?,?,?, NOW())',
            [
                current_user()['email'], $result['dish'], $result['portion'],
                $result['calories'], $result['proteins'], $result['fats'], $result['carbs'],
                $source, $result['confidence'],
            ]
        );
    }

    json_response([
        'ok'      => true,
        'source'  => $source,
        'result'  => $result,
        'saved'   => $scanId !== null,
        'scan_id' => $scanId,
        'ai'      => ai_usage_status('calorie'),  // остаток после инкремента
    ]);
} catch (Throwable $ex) {
    json_response(['error' => APP_DEBUG ? $ex->getMessage() : 'Не удалось проанализировать. Попробуйте другое фото или описание.'], 500);
}
