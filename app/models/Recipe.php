<?php
/**
 * Recipe Model
 */

namespace App\Models;

use App\Database\Database;

class Recipe {
    private $db;

    public function __construct(Database $db) {
        $this->db = $db;
    }

    public function create($data) {
        return $this->db->insert('recipes', [
            'user_id' => $data['user_id'],
            'title' => $data['title'],
            'description' => $data['description'] ?? null,
            'instructions' => $data['instructions'] ?? null,
            'prep_time' => $data['prep_time'] ?? 15,
            'cook_time' => $data['cook_time'] ?? 30,
            'servings' => $data['servings'] ?? 2,
            'difficulty_level' => $data['difficulty_level'] ?? 'easy',
            'calories' => $data['calories'] ?? 0,
            'protein' => $data['protein'] ?? 0,
            'carbs' => $data['carbs'] ?? 0,
            'fat' => $data['fat'] ?? 0,
            'image_url' => $data['image_url'] ?? null,
            'is_public' => $data['is_public'] ?? true,
            'is_ai_generated' => $data['is_ai_generated'] ?? false,
        ]);
    }

    public function findById($id) {
        $recipe = $this->db->selectOne('recipes', '*', 'id = ?', [$id]);
        
        if ($recipe) {
            $recipe['ingredients'] = $this->getIngredients($id);
            $recipe['author'] = $this->getAuthor($recipe['user_id']);
        }

        return $recipe;
    }

    public function getPublicRecipes($limit = 20, $offset = 0) {
        $sql = "SELECT * FROM recipes WHERE is_public = TRUE ORDER BY created_at DESC LIMIT ? OFFSET ?";
        return $this->db->query($sql, [$limit, $offset])->fetchAll();
    }

    public function getPopularRecipes($limit = 10) {
        $sql = "SELECT * FROM recipes WHERE is_public = TRUE ORDER BY views_count DESC, likes_count DESC LIMIT ?";
        return $this->db->query($sql, [$limit])->fetchAll();
    }

    public function getAIGeneratedRecipes($limit = 10) {
        $sql = "SELECT * FROM recipes WHERE is_public = TRUE AND is_ai_generated = TRUE ORDER BY created_at DESC LIMIT ?";
        return $this->db->query($sql, [$limit])->fetchAll();
    }

    public function getUserRecipes($userId) {
        $sql = "SELECT * FROM recipes WHERE user_id = ? ORDER BY created_at DESC";
        return $this->db->query($sql, [$userId])->fetchAll();
    }

    public function searchByTitle($query, $limit = 20) {
        $sql = "SELECT * FROM recipes WHERE is_public = TRUE AND title LIKE ? ORDER BY created_at DESC LIMIT ?";
        return $this->db->query($sql, ['%' . $query . '%', $limit])->fetchAll();
    }

    public function searchByDifficulty($difficulty, $limit = 20) {
        $sql = "SELECT * FROM recipes WHERE is_public = TRUE AND difficulty_level = ? ORDER BY created_at DESC LIMIT ?";
        return $this->db->query($sql, [$difficulty, $limit])->fetchAll();
    }

    public function getIngredients($recipeId) {
        $sql = "SELECT * FROM recipe_ingredients WHERE recipe_id = ? ORDER BY order_index ASC";
        return $this->db->query($sql, [$recipeId])->fetchAll();
    }

    public function addIngredient($recipeId, $name, $quantity, $unit) {
        return $this->db->insert('recipe_ingredients', [
            'recipe_id' => $recipeId,
            'ingredient_name' => $name,
            'quantity' => $quantity,
            'unit' => $unit,
        ]);
    }

    public function getAuthor($userId) {
        return $this->db->selectOne('users', ['id', 'username', 'avatar_url'], 'id = ?', [$userId]);
    }

    public function incrementViews($recipeId) {
        $sql = "UPDATE recipes SET views_count = views_count + 1 WHERE id = ?";
        return $this->db->query($sql, [$recipeId]);
    }

    public function update($id, $data) {
        return $this->db->update('recipes', $data, 'id = ?', [$id]);
    }

    public function delete($id) {
        $this->db->delete('recipe_ingredients', 'recipe_id = ?', [$id]);
        $this->db->delete('ratings', 'recipe_id = ?', [$id]);
        $this->db->delete('likes', 'recipe_id = ?', [$id]);
        $this->db->delete('comments', 'recipe_id = ?', [$id]);
        return $this->db->delete('recipes', 'id = ?', [$id]);
    }
}
