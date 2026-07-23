<?php
require_once __DIR__ . '/../config/config.php';
require_login();

$u = current_user();
$user_id = $u['id'];
$user_email = $u['email'];

// Получаем статистику пользователя
$stats = db_one('SELECT * FROM user_stats WHERE user_id=?', [$user_id]) ?? [];

// Рецепты пользователя
$my_recipes = db_all(
    'SELECT id, title, image_url, likes_count, views_count, created_at 
     FROM recipes WHERE author_id=? OR author_email=? 
     ORDER BY created_at DESC LIMIT 12',
    [$user_id, $user_email]
);

// Комментарии пользователя
$my_comments = db_all(
    'SELECT c.id, c.text, c.rating, c.created_at, c.recipe_id, r.title as recipe_title
     FROM comments c
     LEFT JOIN recipes r ON c.recipe_id = r.id
     WHERE c.user_email=?
     ORDER BY c.created_at DESC LIMIT 10',
    [$user_email]
);

// Друзья
$friends = db_all(
    'SELECT u.id, u.email, u.display_name, u.avatar_emoji, 
            (SELECT COUNT(*) FROM recipes WHERE author_id = u.id OR author_email = u.email) as recipe_count
     FROM friendships f
     LEFT JOIN users u ON f.friend_email = u.email
     WHERE f.user_email = ? AND f.status = "accepted"
     ORDER BY u.display_name',
    [$user_email]
);

// Вся активность (рецепты, комментарии, челленджи, посты в сообществах)
$activity = [];

// Добавляем рецепты
foreach ($my_recipes as $recipe) {
    $activity[] = [
        'type' => 'recipe',
        'title' => $recipe['title'],
        'date' => $recipe['created_at'],
        'data' => $recipe
    ];
}

// Добавляем комментарии
foreach ($my_comments as $comment) {
    $activity[] = [
        'type' => 'comment',
        'title' => 'Комментарий к ' . ($comment['recipe_title'] ?? 'рецепту'),
        'date' => $comment['created_at'],
        'data' => $comment
    ];
}

// Добавляем посты в сообществах
$community_posts = db_all(
    'SELECT cp.id, cp.title, cp.content, cp.created_at, c.name as community_name
     FROM community_posts cp
     LEFT JOIN communities c ON cp.community_id = c.id
     WHERE cp.user_email = ?
     ORDER BY cp.created_at DESC LIMIT 5',
    [$user_email]
);

foreach ($community_posts as $post) {
    $activity[] = [
        'type' => 'post',
        'title' => $post['title'] ?? 'Пост в сообществе ' . ($post['community_name'] ?? ''),
        'date' => $post['created_at'],
        'data' => $post
    ];
}

// Челленджи
$challenges = db_all(
    'SELECT ch.id, ch.title, ch.slug, cp.progress, cp.completed, cp.joined_at
     FROM challenge_participants cp
     LEFT JOIN challenges ch ON cp.challenge_id = ch.id
     WHERE cp.user_email = ?
     ORDER BY cp.joined_at DESC LIMIT 5',
    [$user_email]
);

foreach ($challenges as $challenge) {
    $activity[] = [
        'type' => 'challenge',
        'title' => $challenge['title'] ?? 'Челлендж',
        'date' => $challenge['joined_at'],
        'data' => $challenge
    ];
}

// Сортируем активность по дате (новая сверху)
usort($activity, function($a, $b) {
    return strtotime($b['date']) - strtotime($a['date']);
});
$activity = array_slice($activity, 0, 20); // Последние 20 активностей

// Обработка загрузки аватара
$upload_error = '';
$avatar_url = $u['avatar_emoji'] ?? '👨‍🍳';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['avatar'])) {
    $file = $_FILES['avatar'];
    
    if ($file['error'] === UPLOAD_ERR_OK) {
        $allowed_types = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
        
        if (!in_array($file['type'], $allowed_types)) {
            $upload_error = 'Только JPEG, PNG, GIF и WebP изображения разрешены';
        } elseif ($file['size'] > 5 * 1024 * 1024) {
            $upload_error = 'Файл слишком большой (макс. 5MB)';
        } else {
            $upload_dir = __DIR__ . '/../uploads/avatar';
            if (!is_dir($upload_dir)) mkdir($upload_dir, 0755, true);
            
            $filename = 'user_' . $user_id . '_' . time() . '.' . pathinfo($file['name'], PATHINFO_EXTENSION);
            $filepath = $upload_dir . '/' . $filename;
            
            if (move_uploaded_file($file['tmp_name'], $filepath)) {
                $avatar_url = '/uploads/avatar/' . $filename;
                db_exec(
                    'UPDATE users SET avatar_emoji = ? WHERE id = ?',
                    [$avatar_url, $user_id]
                );
            } else {
                $upload_error = 'Ошибка при загрузке файла';
            }
        }
    }
}

// Обработка обновления данных профиля
$profile_error = '';
$profile_success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_profile'])) {
    if (!csrf_check($_POST['csrf'] ?? '')) {
        $profile_error = 'Сессия истекла';
    } else {
        $new_name = trim($_POST['display_name'] ?? $u['name']);
        $new_bio = trim($_POST['bio'] ?? '');
        
        if (mb_strlen($new_name) < 2) {
            $profile_error = 'Имя должно быть не короче 2 символов';
        } else {
            db_exec(
                'UPDATE users SET display_name = ?, bio = ? WHERE id = ?',
                [$new_name, $new_bio, $user_id]
            );
            $profile_success = 'Профиль успешно обновлён';
            $u['name'] = $new_name;
        }
    }
}

$pageTitle = 'Мой профиль';
require_once __DIR__ . '/../includes/header.php';
?>

<section class="max-w-6xl mx-auto px-4 sm:px-6 py-8">
    
    <!-- Профиль хедер -->
    <div class="bg-gradient-to-r from-amber-50 to-orange-50 rounded-3xl shadow-md p-8 mb-8" data-aos="fade-up">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
            
            <!-- Аватар и загрузка -->
            <div class="flex flex-col items-center justify-center">
                <div class="relative mb-4">
                    <?php if (strpos($avatar_url, '/') === 0): ?>
                        <img src="<?= e($avatar_url) ?>" alt="Avatar" class="w-32 h-32 rounded-full object-cover border-4 border-amber-300 shadow-lg">
                    <?php else: ?>
                        <div class="w-32 h-32 rounded-full bg-amber-200 flex items-center justify-center text-6xl border-4 border-amber-300 shadow-lg">
                            <?= $avatar_url ?>
                        </div>
                    <?php endif; ?>
                    <label for="avatar-upload" class="absolute bottom-0 right-0 bg-amber-400 p-2 rounded-full cursor-pointer hover:bg-amber-500 transition shadow-md">
                        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                        </svg>
                    </label>
                </div>
                <form id="avatar-form" method="post" enctype="multipart/form-data" class="hidden">
                    <input type="file" id="avatar-upload" name="avatar" accept="image/*" onchange="document.getElementById('avatar-form').submit()">
                </form>
                <p class="text-xs text-gray-500 text-center">JPG, PNG, GIF, WebP до 5MB</p>
            </div>

            <!-- Данные профиля (редактирование) -->
            <div class="md:col-span-3">
                <?php if ($profile_error): ?>
                    <div class="mb-4 px-4 py-2 rounded-xl bg-rose-100 text-rose-700 text-sm"><?= e($profile_error) ?></div>
                <?php endif; ?>
                <?php if ($profile_success): ?>
                    <div class="mb-4 px-4 py-2 rounded-xl bg-emerald-100 text-emerald-700 text-sm"><?= e($profile_success) ?></div>
                <?php endif; ?>
                <?php if ($upload_error): ?>
                    <div class="mb-4 px-4 py-2 rounded-xl bg-rose-100 text-rose-700 text-sm"><?= e($upload_error) ?></div>
                <?php endif; ?>

                <form method="post" class="space-y-3">
                    <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">
                    <input type="hidden" name="update_profile" value="1">
                    
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-1">Имя</label>
                        <input type="text" name="display_name" value="<?= e($u['name'] ?? '') ?>" required
                               class="w-full px-3 py-2 rounded-lg border border-gray-200 outline-none focus:border-amber-400">
                    </div>
                    
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-1">Email</label>
                        <input type="email" value="<?= e($user_email) ?>" disabled
                               class="w-full px-3 py-2 rounded-lg border border-gray-300 bg-gray-100 text-gray-600">
                    </div>
                    
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-1">Биография</label>
                        <textarea name="bio" rows="3" maxlength="500"
                                  class="w-full px-3 py-2 rounded-lg border border-gray-200 outline-none focus:border-amber-400 resize-none"
                                  placeholder="Расскажите о себе..."><?= e($u['bio'] ?? '') ?></textarea>
                        <p class="text-xs text-gray-400 mt-1">Макс. 500 символов</p>
                    </div>
                    
                    <button type="submit" class="w-full py-2 rounded-lg bg-gradient-to-r from-amber-400 to-orange-400 text-amber-900 font-semibold hover:shadow-md transition">
                        Сохранить
                    </button>
                </form>
            </div>
        </div>
    </div>

    <!-- Статистика -->
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 mb-8" data-aos="fade-up">
        <div class="bg-white rounded-2xl shadow-md p-4 text-center">
            <div class="text-2xl font-bold text-amber-600"><?= (int)($stats['total_points'] ?? 0) ?></div>
            <div class="text-xs text-gray-500">очков</div>
        </div>
        <div class="bg-white rounded-2xl shadow-md p-4 text-center">
            <div class="text-2xl font-bold text-emerald-600"><?= count($my_recipes) ?></div>
            <div class="text-xs text-gray-500">рецептов</div>
        </div>
        <div class="bg-white rounded-2xl shadow-md p-4 text-center">
            <div class="text-2xl font-bold text-violet-600"><?= (int)($stats['level'] ?? 1) ?></div>
            <div class="text-xs text-gray-500">уровень</div>
        </div>
        <div class="bg-white rounded-2xl shadow-md p-4 text-center">
            <div class="text-2xl font-bold text-blue-600"><?= count($friends) ?></div>
            <div class="text-xs text-gray-500">друзей</div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        
        <!-- Основной контент (2/3) -->
        <div class="lg:col-span-2 space-y-8">
            
            <!-- Мои рецепты -->
            <div data-aos="fade-up">
                <div class="flex items-center justify-between mb-4">
                    <h2 class="text-xl font-extrabold text-gray-800">📖 Мои рецепты</h2>
                    <a href="<?= url('create-recipe') ?>" class="text-sm text-amber-600 font-semibold hover:underline">Создать →</a>
                </div>
                <?php if ($my_recipes): ?>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <?php foreach ($my_recipes as $recipe): ?>
                            <div class="bg-white rounded-2xl shadow-md overflow-hidden hover:shadow-lg transition">
                                <?php if ($recipe['image_url']): ?>
                                    <img src="<?= e($recipe['image_url']) ?>" alt="<?= e($recipe['title']) ?>" class="w-full h-40 object-cover">
                                <?php else: ?>
                                    <div class="w-full h-40 bg-gradient-to-br from-amber-100 to-orange-100 flex items-center justify-center text-3xl">🍽️</div>
                                <?php endif; ?>
                                <div class="p-3">
                                    <a href="<?= url('recipe-detail?id=' . $recipe['id']) ?>" class="font-semibold text-gray-800 hover:text-amber-600 line-clamp-2">
                                        <?= e($recipe['title']) ?>
                                    </a>
                                    <div class="flex gap-3 mt-2 text-xs text-gray-500">
                                        <span>👁️ <?= (int)$recipe['views_count'] ?></span>
                                        <span>❤️ <?= (int)$recipe['likes_count'] ?></span>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <div class="bg-white rounded-2xl shadow-md p-6 text-center text-gray-400">
                        У вас пока нет рецептов. <a href="<?= url('create-recipe') ?>" class="text-amber-600 font-semibold">Создать первый →</a>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Мои комментарии -->
            <div data-aos="fade-up">
                <h2 class="text-xl font-extrabold text-gray-800 mb-4">💬 Мои комментарии</h2>
                <?php if ($my_comments): ?>
                    <div class="space-y-3">
                        <?php foreach ($my_comments as $comment): ?>
                            <div class="bg-white rounded-2xl shadow-md p-4">
                                <div class="flex gap-2 items-start mb-2">
                                    <?php if ($comment['rating']): ?>
                                        <span class="text-sm text-yellow-500">
                                            <?php for ($i = 0; $i < $comment['rating']; $i++): ?> ⭐<?php endfor; ?>
                                        </span>
                                    <?php endif; ?>
                                </div>
                                <p class="text-gray-700 text-sm line-clamp-3"><?= e($comment['text']) ?></p>
                                <div class="flex items-center justify-between mt-2">
                                    <a href="<?= url('recipe-detail?id=' . $comment['recipe_id']) ?>" class="text-xs text-amber-600 hover:underline font-semibold">
                                        К рецепту: <?= e($comment['recipe_title']) ?>
                                    </a>
                                    <span class="text-xs text-gray-400"><?= date('d.m.Y', strtotime($comment['created_at'])) ?></span>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <div class="bg-white rounded-2xl shadow-md p-6 text-center text-gray-400">
                        Вы еще не оставили комментариев
                    </div>
                <?php endif; ?>
            </div>

            <!-- Вся активность -->
            <div data-aos="fade-up">
                <h2 class="text-xl font-extrabold text-gray-800 mb-4">⚡ Активность</h2>
                <?php if ($activity): ?>
                    <div class="space-y-3">
                        <?php foreach ($activity as $item): ?>
                            <div class="bg-white rounded-2xl shadow-md p-4 flex gap-3 items-start">
                                <div class="text-2xl flex-shrink-0">
                                    <?php 
                                    switch ($item['type']) {
                                        case 'recipe': echo '📖'; break;
                                        case 'comment': echo '💬'; break;
                                        case 'post': echo '📢'; break;
                                        case 'challenge': echo '🏆'; break;
                                        default: echo '⭐';
                                    }
                                    ?>
                                </div>
                                <div class="flex-1 min-w-0">
                                    <p class="font-semibold text-gray-800 text-sm"><?= e($item['title']) ?></p>
                                    <p class="text-xs text-gray-400 mt-1">
                                        <?php 
                                        $date = new DateTime($item['date']);
                                        $now = new DateTime();
                                        $diff = $now->diff($date);
                                        
                                        if ($diff->days == 0) {
                                            echo 'сегодня';
                                        } elseif ($diff->days == 1) {
                                            echo 'вчера';
                                        } else {
                                            echo $diff->days . ' дн. назад';
                                        }
                                        ?>
                                    </p>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <div class="bg-white rounded-2xl shadow-md p-6 text-center text-gray-400">
                        Активности нет
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Боковая панель (1/3) -->
        <div class="space-y-6">
            
            <!-- Друзья -->
            <div class="bg-white rounded-2xl shadow-md p-6" data-aos="fade-left">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="font-bold text-gray-800">👥 Друзья</h3>
                    <span class="text-sm text-gray-500"><?= count($friends) ?></span>
                </div>
                
                <?php if ($friends): ?>
                    <div class="space-y-3">
                        <?php foreach ($friends as $friend): ?>
                            <div class="flex items-center gap-3 p-2 rounded-lg hover:bg-gray-50 cursor-pointer transition">
                                <div class="w-10 h-10 rounded-full bg-amber-100 flex items-center justify-center text-lg flex-shrink-0">
                                    <?= $friend['avatar_emoji'] ?? '👤' ?>
                                </div>
                                <div class="flex-1 min-w-0">
                                    <p class="text-sm font-semibold text-gray-800 truncate"><?= e($friend['display_name']) ?></p>
                                    <p class="text-xs text-gray-400"><?= (int)$friend['recipe_count'] ?> рецептов</p>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    <a href="<?= url('friends') ?>" class="block mt-4 text-center text-sm text-amber-600 font-semibold hover:underline">
                        Все друзья →
                    </a>
                <?php else: ?>
                    <p class="text-sm text-gray-400 text-center py-3">У вас еще нет друзей</p>
                    <a href="<?= url('friends') ?>" class="block text-center text-sm text-amber-600 font-semibold hover:underline">
                        Найти друзей →
                    </a>
                <?php endif; ?>
            </div>

            <!-- Достижения / Быстрые ссылки -->
            <div class="bg-white rounded-2xl shadow-md p-6 space-y-2" data-aos="fade-left">
                <h3 class="font-bold text-gray-800 mb-4">⚙️ Меню</h3>
                <a href="<?= url('food-diary') ?>" class="block px-3 py-2 rounded-lg hover:bg-amber-50 text-gray-700 text-sm font-semibold transition">
                    📊 Дневник питания
                </a>
                <a href="<?= url('saved-recipes') ?>" class="block px-3 py-2 rounded-lg hover:bg-amber-50 text-gray-700 text-sm font-semibold transition">
                    ❤️ Сохраненные рецепты
                </a>
                <a href="<?= url('my-cookbooks') ?>" class="block px-3 py-2 rounded-lg hover:bg-amber-50 text-gray-700 text-sm font-semibold transition">
                    📚 Мои кулинарные книги
                </a>
                <a href="<?= url('billing-history') ?>" class="block px-3 py-2 rounded-lg hover:bg-amber-50 text-gray-700 text-sm font-semibold transition">
                    💳 История платежей
                </a>
                <hr class="my-2">
                <a href="<?= url('logout') ?>" class="block px-3 py-2 rounded-lg hover:bg-rose-50 text-rose-700 text-sm font-semibold transition">
                    🚪 Выход
                </a>
            </div>
        </div>
    </div>
</section>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
