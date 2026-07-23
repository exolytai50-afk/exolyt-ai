-- phpMyAdmin SQL Dump
-- version 5.2.1-1.el8
-- https://www.phpmyadmin.net/
--
-- Хост: localhost
-- Время создания: Июл 23 2026 г., 17:48
-- Версия сервера: 8.0.36-cll-lve
-- Версия PHP: 7.2.24

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- База данных: `exolyt-ai-2`
--

-- --------------------------------------------------------

--
-- Структура таблицы `ai_usage`
--

CREATE TABLE `ai_usage` (
  `id` int NOT NULL,
  `identifier` varchar(120) COLLATE utf8mb4_unicode_ci NOT NULL,
  `feature` varchar(40) COLLATE utf8mb4_unicode_ci NOT NULL,
  `used_date` date NOT NULL,
  `used_count` int NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Структура таблицы `calorie_scans`
--

CREATE TABLE `calorie_scans` (
  `id` int NOT NULL,
  `user_email` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `dish` varchar(200) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `portion` varchar(120) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `calories` int NOT NULL DEFAULT '0',
  `proteins` int NOT NULL DEFAULT '0',
  `fats` int NOT NULL DEFAULT '0',
  `carbs` int NOT NULL DEFAULT '0',
  `source` enum('photo','text') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'photo',
  `confidence` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Структура таблицы `challenges`
--

CREATE TABLE `challenges` (
  `id` int NOT NULL,
  `title` varchar(160) COLLATE utf8mb4_unicode_ci NOT NULL,
  `slug` varchar(160) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `goal` varchar(200) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `reward_points` int NOT NULL DEFAULT '0',
  `difficulty` enum('easy','medium','hard') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'easy',
  `starts_at` date DEFAULT NULL,
  `ends_at` date DEFAULT NULL,
  `participants` int NOT NULL DEFAULT '0',
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Дамп данных таблицы `challenges`
--

INSERT INTO `challenges` (`id`, `title`, `slug`, `description`, `goal`, `reward_points`, `difficulty`, `starts_at`, `ends_at`, `participants`, `created_at`) VALUES
(1, '7 дней без сахара', '7-days-no-sugar', 'Неделя блюд без добавленного сахара. Делись прогрессом каждый день.', 'Пройти 7 дней', 50, 'easy', '2026-07-19', '2026-07-26', 324, '2026-07-19 00:31:14'),
(2, 'Мастер пасты', 'pasta-master', 'Приготовь 5 разных видов пасты и стань мастером итальянской кухни.', 'Приготовить 5 блюд', 100, 'medium', '2026-07-19', '2026-08-18', 187, '2026-07-19 00:31:14'),
(3, 'Завтрак чемпиона', 'champion-breakfast', '30 дней полезных завтраков подряд. Заряжай утро правильно!', 'Готовить 30 дней', 150, 'hard', '2026-07-19', '2026-08-18', 456, '2026-07-19 00:31:14'),
(4, 'Уложись в 500 ккал', 'under-500', 'Придумай и приготовь сытное блюдо не дороже 500 ккал на порцию.', 'Блюдо до 500 ккал', 75, 'medium', '2026-07-19', '2026-08-02', 298, '2026-07-19 00:31:14'),
(5, 'Домашний хлеб', 'homemade-bread', 'Испеки хлеб на закваске с нуля. От опары до румяной корочки.', 'Испечь до 5 хлебов', 120, 'hard', '2026-07-19', '2026-08-18', 142, '2026-07-19 00:31:14'),
(6, 'Кухни мира за неделю', 'world-cuisines', 'Каждый день — блюдо новой национальной кухни. 7 стран за 7 дней.', 'Приготовить 7 блюд', 90, 'medium', '2026-07-19', '2026-07-26', 512, '2026-07-19 00:31:14'),
(7, 'Zero waste готовка', 'zero-waste', 'Готовь без пищевых отходов: используй продукт целиком.', '5 блюд по принципу', 110, 'hard', '2026-07-19', '2026-08-18', 89, '2026-07-19 00:31:14');

-- --------------------------------------------------------

--
-- Структура таблицы `challenge_participants`
--

CREATE TABLE `challenge_participants` (
  `id` int NOT NULL,
  `challenge_id` int NOT NULL,
  `user_email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `progress` int NOT NULL DEFAULT '0',
  `completed` tinyint(1) NOT NULL DEFAULT '0',
  `joined_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Структура таблицы `comments`
--

CREATE TABLE `comments` (
  `id` int NOT NULL,
  `recipe_id` int NOT NULL,
  `user_email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `text` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `rating` int DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Структура таблицы `communities`
--

CREATE TABLE `communities` (
  `id` int NOT NULL,
  `name` varchar(120) COLLATE utf8mb4_unicode_ci NOT NULL,
  `slug` varchar(120) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `cover_emoji` varchar(16) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `members_count` int NOT NULL DEFAULT '0',
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Дамп данных таблицы `communities`
--

INSERT INTO `communities` (`id`, `name`, `slug`, `description`, `cover_emoji`, `members_count`, `created_at`) VALUES
(1, 'Здоровое питание', 'healthy-eating', 'ПП-рецепты, баланс КБЖУ и полезные привычки каждый день.', '🥗', 1284, '2026-07-19 00:31:14'),
(2, 'Итальянская кухня', 'italian', 'Паста, пицца, ризотто и секреты настоящей итальянской кухни.', '🍝', 942, '2026-07-19 00:31:14'),
(3, 'Выпечка и десерты', 'baking', 'Торты, печенье, хлеб на закваске — делимся рецептами и лайфхаками.', '🍰', 1567, '2026-07-19 00:31:14'),
(4, 'Азиатская кухня', 'asian', 'Рамен, суши, вок и специи Азии в вашей тарелке.', '🍜', 811, '2026-07-19 00:31:14'),
(5, 'Веган и растительное', 'vegan', 'Растительные рецепты без компромиссов по вкусу.', '🌱', 673, '2026-07-19 00:31:14'),
(6, 'Быстрые ужины', 'quick-dinners', 'Готовим вкусно за 30 минут после рабочего дня.', '⚡', 2103, '2026-07-19 00:31:14'),
(7, 'Грузинская кухня', 'georgian', 'Хачапури, хинкали, аджапсандали и грузинское гостеприимство.', '🧆', 588, '2026-07-19 00:31:14'),
(8, 'Домашние заготовки', 'preserves', 'Соленья, варенье, маринады — заготовки на весь год.', '🫙', 449, '2026-07-19 00:31:14');

-- --------------------------------------------------------

--
-- Структура таблицы `community_members`
--

CREATE TABLE `community_members` (
  `id` int NOT NULL,
  `community_id` int NOT NULL,
  `user_email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `joined_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Структура таблицы `community_posts`
--

CREATE TABLE `community_posts` (
  `id` int NOT NULL,
  `community_id` int NOT NULL,
  `user_email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `title` varchar(200) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `content` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `likes_count` int NOT NULL DEFAULT '0',
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Структура таблицы `cookbooks`
--

CREATE TABLE `cookbooks` (
  `id` int NOT NULL,
  `author_email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `title` varchar(200) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `slug` varchar(200) COLLATE utf8mb4_unicode_ci NOT NULL,
  `recipes_json` json DEFAULT NULL,
  `is_public` tinyint(1) NOT NULL DEFAULT '1',
  `likes_count` int NOT NULL DEFAULT '0',
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Структура таблицы `favorites`
--

CREATE TABLE `favorites` (
  `id` int NOT NULL,
  `user_email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `recipe_id` int NOT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Структура таблицы `food_diary`
--

CREATE TABLE `food_diary` (
  `id` int NOT NULL,
  `user_email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `recipe_id` int DEFAULT NULL,
  `dish_name` varchar(200) COLLATE utf8mb4_unicode_ci NOT NULL,
  `calories` int NOT NULL DEFAULT '0',
  `proteins` int NOT NULL DEFAULT '0',
  `fats` int NOT NULL DEFAULT '0',
  `carbs` int NOT NULL DEFAULT '0',
  `meal_type` enum('breakfast','lunch','dinner','snack') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'lunch',
  `logged_at` date NOT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Структура таблицы `friendships`
--

CREATE TABLE `friendships` (
  `id` int NOT NULL,
  `user_email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `friend_email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `status` enum('pending','accepted','blocked') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pending',
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Структура таблицы `leaderboard`
--

CREATE TABLE `leaderboard` (
  `id` int NOT NULL,
  `user_email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `points` int NOT NULL DEFAULT '0',
  `user_rank` int NOT NULL DEFAULT '0',
  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Структура таблицы `meal_plans`
--

CREATE TABLE `meal_plans` (
  `id` int NOT NULL,
  `user_email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `title` varchar(200) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `recipes_json` json DEFAULT NULL,
  `start_date` date NOT NULL,
  `end_date` date NOT NULL,
  `is_public` tinyint(1) NOT NULL DEFAULT '0',
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Структура таблицы `notifications`
--

CREATE TABLE `notifications` (
  `id` int NOT NULL,
  `user_email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `title` varchar(160) COLLATE utf8mb4_unicode_ci NOT NULL,
  `message` text COLLATE utf8mb4_unicode_ci,
  `type` varchar(30) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'system',
  `is_read` tinyint(1) NOT NULL DEFAULT '0',
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Структура таблицы `promo_codes`
--

CREATE TABLE `promo_codes` (
  `id` int NOT NULL,
  `code` varchar(40) COLLATE utf8mb4_unicode_ci NOT NULL,
  `discount_type` enum('percent','fixed') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'percent',
  `discount_val` decimal(10,2) NOT NULL,
  `first_only` tinyint(1) NOT NULL DEFAULT '1',
  `max_uses` int DEFAULT NULL,
  `used_count` int NOT NULL DEFAULT '0',
  `valid_until` date DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Дамп данных таблицы `promo_codes`
--

INSERT INTO `promo_codes` (`id`, `code`, `discount_type`, `discount_val`, `first_only`, `max_uses`, `used_count`, `valid_until`, `is_active`, `created_at`) VALUES
(1, 'WELCOME20', 'percent', 20.00, 1, 1000, 0, '2026-10-17', 1, '2026-07-19 00:31:14'),
(2, 'COOK100', 'fixed', 100.00, 1, 500, 0, '2026-09-17', 1, '2026-07-19 00:31:14'),
(3, 'FIRST50', 'percent', 50.00, 1, 100, 0, '2026-08-18', 1, '2026-07-19 00:31:14');

-- --------------------------------------------------------

--
-- Структура таблицы `recipes`
--

CREATE TABLE `recipes` (
  `id` int NOT NULL,
  `slug` varchar(200) COLLATE utf8mb4_unicode_ci NOT NULL,
  `author_email` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `title` varchar(200) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `ingredients` json DEFAULT NULL,
  `steps` json DEFAULT NULL,
  `cuisine` varchar(80) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `difficulty` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT 'Легко',
  `prep_time` int NOT NULL DEFAULT '0',
  `cook_time` int NOT NULL DEFAULT '0',
  `servings` int NOT NULL DEFAULT '1',
  `calories` int DEFAULT NULL,
  `proteins` int DEFAULT NULL,
  `fats` int DEFAULT NULL,
  `carbs` int DEFAULT NULL,
  `diet_type` json DEFAULT NULL,
  `tags` json DEFAULT NULL,
  `season` json DEFAULT NULL,
  `image_url` varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `is_ai` tinyint(1) NOT NULL DEFAULT '0',
  `is_ai_generated` tinyint(1) NOT NULL DEFAULT '0',
  `likes_count` int NOT NULL DEFAULT '0',
  `views_count` int NOT NULL DEFAULT '0',
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Дамп данных таблицы `recipes`
--

INSERT INTO `recipes` (`id`, `slug`, `author_email`, `title`, `description`, `ingredients`, `steps`, `cuisine`, `difficulty`, `prep_time`, `cook_time`, `servings`, `calories`, `proteins`, `fats`, `carbs`, `diet_type`, `tags`, `season`, `image_url`, `is_ai`, `is_ai_generated`, `likes_count`, `views_count`, `created_at`, `updated_at`) VALUES
(1, 'pasta-carbonara', 'demo@cookai.ru', 'Паста Карбонара', 'Классическая римская паста с гуанчиале, яйцом и пекорино.', '[{\"name\": \"Спагетти\", \"unit\": \"г\", \"amount\": \"200\"}, {\"name\": \"Гуанчиале\", \"unit\": \"г\", \"amount\": \"100\"}, {\"name\": \"Яичные желтки\", \"unit\": \"шт\", \"amount\": \"3\"}, {\"name\": \"Пекорино\", \"unit\": \"г\", \"amount\": \"50\"}, {\"name\": \"Чёрный перец\", \"unit\": \"\", \"amount\": \"по вкусу\"}]', '[{\"tip\": \"Вода должна быть подсолённой как море\", \"order\": 1, \"instruction\": \"Отварите спагетти al dente\", \"timer_minutes\": 10}, {\"tip\": \"Не используйте бекон\", \"order\": 2, \"instruction\": \"Обжарьте гуанчиале до хруста\", \"timer_minutes\": 5}, {\"tip\": \"Добавьте перец\", \"order\": 3, \"instruction\": \"Смешайте желтки с сыром\", \"timer_minutes\": 2}, {\"tip\": \"Мешайте быстро\", \"order\": 4, \"instruction\": \"Соедините всё с пастой\", \"timer_minutes\": 2}]', 'Итальянская', 'Средне', 5, 25, 2, 620, 24, 30, 65, '[\"мясо\", \"паста\"]', '[\"паста\", \"классика\", \"итальянская\"]', '[\"Зима\", \"Осень\", \"Весна\"]', NULL, 0, 0, 0, 0, '2026-07-19 00:31:14', '2026-07-19 00:31:14'),
(2, 'greek-salad', 'demo@cookai.ru', 'Греческий салат', 'Свежий салат с фетой, оливками и оливковым маслом.', '[{\"name\": \"Помидоры\", \"unit\": \"шт\", \"amount\": \"3\"}, {\"name\": \"Огурцы\", \"unit\": \"шт\", \"amount\": \"2\"}, {\"name\": \"Фета\", \"unit\": \"г\", \"amount\": \"150\"}, {\"name\": \"Маслины\", \"unit\": \"г\", \"amount\": \"100\"}, {\"name\": \"Лук красный\", \"unit\": \"шт\", \"amount\": \"1/4\"}, {\"name\": \"Масло оливковое\", \"unit\": \"ст.л.\", \"amount\": \"3\"}, {\"name\": \"Орегано\", \"unit\": \"ч.л.\", \"amount\": \"1\"}]', '[{\"tip\": \"Помидоры в четвертинки\", \"order\": 1, \"instruction\": \"Нарежьте овощи крупно\", \"timer_minutes\": 5}, {\"tip\": \"Лук замочите\", \"order\": 2, \"instruction\": \"Добавьте маслины и лук\", \"timer_minutes\": 2}, {\"tip\": \"Поломайте на куски\", \"order\": 3, \"instruction\": \"Положите фету\", \"timer_minutes\": 1}, {\"tip\": \"Настояться 10 минут\", \"order\": 4, \"instruction\": \"Полейте маслом и орегано\", \"timer_minutes\": 1}]', 'Средиземноморская', 'Легко', 10, 15, 2, 320, 10, 24, 14, '[\"овощи\", \"здоровое\"]', '[\"салат\", \"овощи\", \"низкокалорийно\"]', '[\"Лето\"]', NULL, 0, 0, 0, 0, '2026-07-19 00:31:14', '2026-07-19 00:31:14');

-- --------------------------------------------------------

--
-- Структура таблицы `shopping_lists`
--

CREATE TABLE `shopping_lists` (
  `id` int NOT NULL,
  `user_email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `recipe_id` int DEFAULT NULL,
  `items_json` json DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Структура таблицы `subscriptions`
--

CREATE TABLE `subscriptions` (
  `id` int NOT NULL,
  `parent_id` int DEFAULT NULL,
  `user_email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `status` enum('pending','active','canceled','expired','refunded') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pending',
  `plan` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `months` int NOT NULL DEFAULT '1',
  `payment_id` varchar(64) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `payment_method_id` varchar(64) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `auto_renew` tinyint(1) NOT NULL DEFAULT '0',
  `renew_attempts` int NOT NULL DEFAULT '0',
  `renewal_notified` tinyint(1) NOT NULL DEFAULT '0',
  `amount` decimal(10,2) NOT NULL DEFAULT '0.00',
  `promo_code` varchar(40) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `original_amount` decimal(10,2) DEFAULT NULL,
  `refunded_amount` decimal(10,2) NOT NULL DEFAULT '0.00',
  `refund_id` varchar(64) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `receipt_number` varchar(32) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `subscription_end_date` date DEFAULT NULL,
  `next_charge_date` date DEFAULT NULL,
  `paid_at` datetime DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Структура таблицы `users`
--

CREATE TABLE `users` (
  `id` int NOT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `password_hash` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `display_name` varchar(120) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `avatar_emoji` varchar(16) COLLATE utf8mb4_unicode_ci DEFAULT 0xF09FA791E2808DF09F8DB3,
  `bio` varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `role` enum('user','admin') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'user',
  `points` int NOT NULL DEFAULT '0',
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `last_login_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Дамп данных таблицы `users`
--

INSERT INTO `users` (`id`, `email`, `password_hash`, `display_name`, `avatar_emoji`, `bio`, `role`, `points`, `created_at`, `last_login_at`) VALUES
(1, 'demo@cookai.ru', '$2y$10$e0NR0nQ7cJ7wq4Qw3q1vEuJ1kxq1Q2m3n4b5v6c7x8z9a0s1d2f3', 'Демо Повар', '👨‍🍳', 'Люблю готовить и делиться рецептами.', 'user', 1250, '2026-07-19 00:31:14', NULL);

--
-- Индексы сохранённых таблиц
--

--
-- Индексы таблицы `ai_usage`
--
ALTER TABLE `ai_usage`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uniq_usage` (`identifier`,`feature`,`used_date`),
  ADD KEY `idx_date` (`used_date`);

--
-- Индексы таблицы `calorie_scans`
--
ALTER TABLE `calorie_scans`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_user_date` (`user_email`,`created_at`);

--
-- Индексы таблицы `challenges`
--
ALTER TABLE `challenges`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `slug` (`slug`);

--
-- Индексы таблицы `challenge_participants`
--
ALTER TABLE `challenge_participants`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uniq_part` (`challenge_id`,`user_email`);

--
-- Индексы таблицы `comments`
--
ALTER TABLE `comments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_recipe` (`recipe_id`),
  ADD KEY `idx_user` (`user_email`);

--
-- Индексы таблицы `communities`
--
ALTER TABLE `communities`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `slug` (`slug`);

--
-- Индексы таблицы `community_members`
--
ALTER TABLE `community_members`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uniq_member` (`community_id`,`user_email`);

--
-- Индексы таблицы `community_posts`
--
ALTER TABLE `community_posts`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_community` (`community_id`),
  ADD KEY `idx_user` (`user_email`);

--
-- Индексы таблицы `cookbooks`
--
ALTER TABLE `cookbooks`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `slug` (`slug`),
  ADD KEY `idx_author` (`author_email`),
  ADD KEY `idx_created` (`created_at`);

--
-- Индексы таблицы `favorites`
--
ALTER TABLE `favorites`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uniq_fav` (`user_email`,`recipe_id`),
  ADD KEY `idx_user` (`user_email`);

--
-- Индексы таблицы `food_diary`
--
ALTER TABLE `food_diary`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_user_date` (`user_email`,`logged_at`),
  ADD KEY `idx_user` (`user_email`);

--
-- Индексы таблицы `friendships`
--
ALTER TABLE `friendships`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uniq_friendship` (`user_email`,`friend_email`),
  ADD KEY `idx_user` (`user_email`),
  ADD KEY `idx_friend` (`friend_email`);

--
-- Индексы таблицы `leaderboard`
--
ALTER TABLE `leaderboard`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `user_email` (`user_email`),
  ADD KEY `idx_points` (`points` DESC),
  ADD KEY `idx_rank` (`user_rank`);

--
-- Индексы таблицы `meal_plans`
--
ALTER TABLE `meal_plans`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_user` (`user_email`),
  ADD KEY `idx_dates` (`start_date`,`end_date`);

--
-- Индексы таблицы `notifications`
--
ALTER TABLE `notifications`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_user_read` (`user_email`,`is_read`),
  ADD KEY `idx_created` (`created_at`);

--
-- Индексы таблицы `promo_codes`
--
ALTER TABLE `promo_codes`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `code` (`code`);

--
-- Индексы таблицы `recipes`
--
ALTER TABLE `recipes`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `slug` (`slug`),
  ADD KEY `idx_author` (`author_email`),
  ADD KEY `idx_cuisine` (`cuisine`),
  ADD KEY `idx_created` (`created_at`),
  ADD KEY `idx_difficulty` (`difficulty`);
ALTER TABLE `recipes` ADD FULLTEXT KEY `idx_search` (`title`,`description`);

--
-- Индексы таблицы `shopping_lists`
--
ALTER TABLE `shopping_lists`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_user` (`user_email`);

--
-- Индексы таблицы `subscriptions`
--
ALTER TABLE `subscriptions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_payment_id` (`payment_id`),
  ADD KEY `idx_user_status` (`user_email`,`status`),
  ADD KEY `idx_autorenew` (`auto_renew`,`status`,`next_charge_date`),
  ADD KEY `idx_notify` (`auto_renew`,`status`,`renewal_notified`,`next_charge_date`);

--
-- Индексы таблицы `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `idx_email` (`email`);

--
-- AUTO_INCREMENT для сохранённых таблиц
--

--
-- AUTO_INCREMENT для таблицы `ai_usage`
--
ALTER TABLE `ai_usage`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT для таблицы `calorie_scans`
--
ALTER TABLE `calorie_scans`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT для таблицы `challenges`
--
ALTER TABLE `challenges`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT для таблицы `challenge_participants`
--
ALTER TABLE `challenge_participants`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT для таблицы `comments`
--
ALTER TABLE `comments`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT для таблицы `communities`
--
ALTER TABLE `communities`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT для таблицы `community_members`
--
ALTER TABLE `community_members`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT для таблицы `community_posts`
--
ALTER TABLE `community_posts`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT для таблицы `cookbooks`
--
ALTER TABLE `cookbooks`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT для таблицы `favorites`
--
ALTER TABLE `favorites`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT для таблицы `food_diary`
--
ALTER TABLE `food_diary`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT для таблицы `friendships`
--
ALTER TABLE `friendships`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT для таблицы `leaderboard`
--
ALTER TABLE `leaderboard`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT для таблицы `meal_plans`
--
ALTER TABLE `meal_plans`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT для таблицы `notifications`
--
ALTER TABLE `notifications`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT для таблицы `promo_codes`
--
ALTER TABLE `promo_codes`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT для таблицы `recipes`
--
ALTER TABLE `recipes`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT для таблицы `shopping_lists`
--
ALTER TABLE `shopping_lists`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT для таблицы `subscriptions`
--
ALTER TABLE `subscriptions`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT для таблицы `users`
--
ALTER TABLE `users`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
