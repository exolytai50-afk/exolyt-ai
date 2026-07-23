<?php
/**
 * CookAI — настройки Yandex AI Studio
 */
declare(strict_types=1);

defined('YANDEX_API_KEY')   || define('YANDEX_API_KEY', 'AQVN27WMtneoptdWWqXZ4RyZzrIa5dUyS9zRwKYu');
defined('YANDEX_FOLDER_ID') || define('YANDEX_FOLDER_ID', 'b1g26lamin6oetbepvee');

// --- LLM URLs ---
defined('YANDEX_COMPLETION_URL') || define('YANDEX_COMPLETION_URL', 'https://llm.api.cloud.yandex.net/foundationModels/v1/completion');

// --- LLM Models ---
// YandexGPT 4 Lite для текстовых запросов
defined('YANDEX_GPT_4_LITE_MODEL') || define('YANDEX_GPT_4_LITE_MODEL', 'yandexgpt-4-lite/latest');

// --- VLM Models ---
// Qwen3.6-35B для анализа изображений (Vision)
defined('QWEN_3_6_MODEL') || define('QWEN_3_6_MODEL', 'qwen3.6-35b-a3b/latest');

// Лимиты загрузки изображений
defined('MAX_IMAGE_SIZE')      || define('MAX_IMAGE_SIZE', 5 * 1024 * 1024);   // 5 МБ
defined('ALLOWED_IMAGE_TYPES') || define('ALLOWED_IMAGE_TYPES', 'image/jpeg,image/png,image/webp');

// --- SMTP (опционально) ---
defined('SMTP_HOST')      || define('SMTP_HOST', '');
defined('SMTP_PORT')      || define('SMTP_PORT', 465);
defined('SMTP_USER')      || define('SMTP_USER', '');
defined('SMTP_PASS')      || define('SMTP_PASS', '');
defined('SMTP_FROM')      || define('SMTP_FROM', 'noreply@cookai.local');
defined('SMTP_FROM_NAME') || define('SMTP_FROM_NAME', 'CookAI');
