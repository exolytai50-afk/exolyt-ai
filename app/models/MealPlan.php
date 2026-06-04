<?php
/**
 * MealPlan Model
 */

namespace App\Models;

use App\Database\Database;

class MealPlan {
    private $db;

    public function __construct(Database $db) {
        $this->db = $db;
    }

    public function create($data) {
        return $this->db->insert('meal_plans', [
            'user_id' => $data['user_id'],
            'title' => $data['title'] ?? 'My Meal Plan',
            'description' => $data['description'] ?? null,
            'goal' => $data['goal'] ?? 'balanced',
            'daily_calories' => $data['daily_calories'] ?? 2000,
            'dietary_restrictions' => $data['dietary_restrictions'] ?? null,
            'start_date' => $data['start_date'] ?? date('Y-m-d'),
        ]);
    }

    public function findById($id) {
        $plan = $this->db->selectOne('meal_plans', '*', 'id = ?', [$id]);
        
        if ($plan) {
            $plan['meals'] = $this->getMeals($id);
        }

        return $plan;
    }

    public function getByUser($userId) {
        $sql = "SELECT * FROM meal_plans WHERE user_id = ? ORDER BY created_at DESC";
        return $this->db->query($sql, [$userId])->fetchAll();
    }

    public function addMealItem($mealPlanId, $recipeId, $dayOfWeek, $mealType) {
        return $this->db->insert('meal_plan_items', [
            'meal_plan_id' => $mealPlanId,
            'recipe_id' => $recipeId,
            'day_of_week' => $dayOfWeek,
            'meal_type' => $mealType,
        ]);
    }

    public function getMeals($mealPlanId) {
        $sql = "SELECT mpi.*, r.title, r.calories FROM meal_plan_items mpi
                JOIN recipes r ON mpi.recipe_id = r.id
                WHERE mpi.meal_plan_id = ?
                ORDER BY mpi.day_of_week, FIELD(mpi.meal_type, 'breakfast', 'lunch', 'dinner', 'snack')";
        return $this->db->query($sql, [$mealPlanId])->fetchAll();
    }

    public function removeMealItem($itemId) {
        return $this->db->delete('meal_plan_items', 'id = ?', [$itemId]);
    }

    public function update($id, $data) {
        return $this->db->update('meal_plans', $data, 'id = ?', [$id]);
    }

    public function delete($id) {
        $this->db->delete('meal_plan_items', 'meal_plan_id = ?', [$id]);
        return $this->db->delete('meal_plans', 'id = ?', [$id]);
    }
}
