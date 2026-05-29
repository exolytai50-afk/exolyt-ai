<?php
/**
 * Cookbook Model
 */

namespace App\Models;

use App\Database\Database;

class Cookbook {
    private $db;

    public function __construct(Database $db) {
        $this->db = $db;
    }

    public function create($data) {
        return $this->db->insert('cookbooks', [
            'user_id' => $data['user_id'],
            'title' => $data['title'],
            'description' => $data['description'] ?? null,
            'cover_image_url' => $data['cover_image_url'] ?? null,
            'is_public' => $data['is_public'] ?? false,
        ]);
    }

    public function findById($id) {
        $cookbook = $this->db->selectOne('cookbooks', '*', 'id = ?', [$id]);
        if ($cookbook) {
            $cookbook['recipes'] = $this->getRecipes($id);
        }
        return $cookbook;
    }

    public function getByUser($userId) {
        $sql = "SELECT * FROM cookbooks WHERE user_id = ? ORDER BY created_at DESC";
        return $this->db->query($sql, [$userId])->fetchAll();
    }

    public function getPublicCookbooks($limit = 20, $offset = 0) {
        $sql = "SELECT * FROM cookbooks WHERE is_public = TRUE ORDER BY created_at DESC LIMIT ? OFFSET ?";
        return $this->db->query($sql, [$limit, $offset])->fetchAll();
    }

    public function addRecipe($cookbookId, $recipeId) {
        return $this->db->insert('cookbook_recipes', [
            'cookbook_id' => $cookbookId,
            'recipe_id' => $recipeId,
        ]);
    }

    public function removeRecipe($cookbookId, $recipeId) {
        return $this->db->delete('cookbook_recipes', 
            'cookbook_id = ? AND recipe_id = ?', 
            [$cookbookId, $recipeId]
        );
    }

    public function getRecipes($cookbookId) {
        $sql = "SELECT r.* FROM recipes r 
                JOIN cookbook_recipes cr ON r.id = cr.recipe_id 
                WHERE cr.cookbook_id = ? 
                ORDER BY cr.added_at DESC";
        return $this->db->query($sql, [$cookbookId])->fetchAll();
    }

    public function update($id, $data) {
        return $this->db->update('cookbooks', $data, 'id = ?', [$id]);
    }

    public function delete($id) {
        return $this->db->delete('cookbooks', 'id = ?', [$id]);
    }
}
