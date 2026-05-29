<?php
/**
 * Main Application Entry Point
 */

session_start();

// Load config
$config = require __DIR__ . '/../app/config/config.php';

// Autoload classes
spl_autoload_register(function($class) {
    $file = __DIR__ . '/../' . str_replace('\\', '/', $class) . '.php';
    if (file_exists($file)) {
        require $file;
    }
});

use App\Core\Router;
use App\Controllers\AuthController;
use App\Controllers\RecipeController;
use App\Controllers\CommunityController;
use App\Controllers\ChallengeController;
use App\Controllers\AIController;

// Set JSON response header
header('Content-Type: application/json');

// Initialize router
$router = new Router();

// Auth routes
$router->post('/api/auth/register', [AuthController::class, 'register']);
$router->post('/api/auth/login', [AuthController::class, 'login']);
$router->post('/api/auth/logout', [AuthController::class, 'logout']);
$router->get('/api/auth/me', [AuthController::class, 'me']);

// Recipe routes
$router->get('/api/recipes', [RecipeController::class, 'getPublicRecipes']);
$router->get('/api/recipes/popular', [RecipeController::class, 'getPopularRecipes']);
$router->get('/api/recipes/ai', [RecipeController::class, 'getAIRecipes']);
$router->get('/api/recipes/{id}', [RecipeController::class, 'getRecipeById']);
$router->post('/api/recipes', [RecipeController::class, 'createRecipe']);
$router->get('/api/recipes/search/{query}', [RecipeController::class, 'searchRecipes']);
$router->get('/api/recipes/difficulty/{difficulty}', [RecipeController::class, 'getRecipesByDifficulty']);

// Community routes
$router->get('/api/communities', [CommunityController::class, 'getAllCommunities']);
$router->get('/api/communities/{id}', [CommunityController::class, 'getCommunityById']);
$router->post('/api/communities', [CommunityController::class, 'createCommunity']);
$router->post('/api/communities/{id}/join', [CommunityController::class, 'joinCommunity']);
$router->post('/api/communities/{id}/leave', [CommunityController::class, 'leaveCommunity']);
$router->get('/api/communities/search/{query}', [CommunityController::class, 'searchCommunities']);

// Challenge routes
$router->get('/api/challenges', [ChallengeController::class, 'getAllChallenges']);
$router->get('/api/challenges/active', [ChallengeController::class, 'getActiveChallenges']);
$router->get('/api/challenges/{id}', [ChallengeController::class, 'getChallengeById']);
$router->post('/api/challenges/{id}/join', [ChallengeController::class, 'joinChallenge']);
$router->post('/api/challenges/{id}/complete', [ChallengeController::class, 'completeChallenge']);
$router->get('/api/challenges/user/my', [ChallengeController::class, 'getUserChallenges']);

// AI routes
$router->post('/api/ai/generate-recipe', [AIController::class, 'generateRecipe']);
$router->post('/api/ai/analyze-food', [AIController::class, 'analyzeFood']);
$router->post('/api/ai/meal-plan', [AIController::class, 'generateMealPlan']);
$router->get('/api/ai/cooking-tips/{query}', [AIController::class, 'getCookingTips']);

// Dispatch request
$method = $_SERVER['REQUEST_METHOD'];
$uri = $_SERVER['REQUEST_URI'];

$router->dispatch($method, $uri);
