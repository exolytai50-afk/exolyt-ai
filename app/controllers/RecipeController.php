<?php
/**
 * Recipe Controller
 */

namespace App\Controllers;

use App\Models\Recipe;
use App\Database\Database;

class RecipeController {
    private $recipeModel;

    public function __construct() {
        $config = require __DIR__ . '/../config/config.php';
        $db = new Database($config['database']);
        $this->recipeModel = new Recipe($db);
    }

    public function getPublicRecipes($page = 1) {
        $limit = 20;
        $offset = ($page - 1) * $limit;
        
        $recipes = $this->recipeModel->getPublicRecipes($limit, $offset);
        echo json_encode([
            'page' => $page,
            'recipes' => $recipes,
        ]);
    }

    public function getPopularRecipes() {
        $recipes = $this->recipeModel->getPopularRecipes(10);
        echo json_encode($recipes);
    }

    public function getRecipeById($id) {
        $recipe = $this->recipeModel->findById($id);
        
        if (!$recipe) {
            http_response_code(404);
            echo json_encode(['error' => 'Recipe not found']);
            return;
        }

        $this->recipeModel->incrementViews($id);
        echo json_encode($recipe);
    }

    public function createRecipe() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['error' => 'Method Not Allowed']);
            return;
        }

        if (!isset($_SESSION['user_id'])) {
            http_response_code(401);
            echo json_encode(['error' => 'Not authenticated']);
            return;
        }

        $input = json_decode(file_get_contents('php://input'), true);

        if (!$input || !isset($input['title'])) {
            http_response_code(400);
            echo json_encode(['error' => 'Missing required fields']);
            return;
        }

        $input['user_id'] = $_SESSION['user_id'];

        try {
            $this->recipeModel->create($input);
            http_response_code(201);
            echo json_encode(['message' => 'Recipe created successfully']);
        } catch (\Exception $e) {
            http_response_code(500);
            echo json_encode(['error' => 'Failed to create recipe']);
        }
    }

    public function searchRecipes($query) {
        $recipes = $this->recipeModel->searchByTitle($query, 20);
        echo json_encode($recipes);
    }

    public function getRecipesByDifficulty($difficulty) {
        $recipes = $this->recipeModel->searchByDifficulty($difficulty, 20);
        echo json_encode($recipes);
    }

    public function getAIRecipes() {
        $recipes = $this->recipeModel->getAIGeneratedRecipes(20);
        echo json_encode($recipes);
    }
}
