<?php
/**
 * AI Service - Integration with AI APIs
 */

namespace App\Services;

class AIService {
    private $openaiKey;
    private $visionKey;

    public function __construct($openaiKey = '', $visionKey = '') {
        $this->openaiKey = $openaiKey ?: getenv('OPENAI_API_KEY');
        $this->visionKey = $visionKey ?: getenv('VISION_API_KEY');
    }

    /**
     * Generate recipe using OpenAI
     */
    public function generateRecipe($constraints) {
        if (!$this->openaiKey) {
            throw new \Exception('OpenAI API key not configured');
        }

        $prompt = "Create a recipe with the following constraints:\n";
        if (!empty($constraints['diet'])) $prompt .= "- Diet: {$constraints['diet']}\n";
        if (!empty($constraints['time'])) $prompt .= "- Time: {$constraints['time']} minutes\n";
        if (!empty($constraints['ingredients'])) $prompt .= "- Ingredients: " . implode(', ', $constraints['ingredients']) . "\n";

        $prompt .= "\nProvide the recipe in JSON format with: title, ingredients (array), instructions (array), prep_time, cook_time, servings, calories, protein, carbs, fat.";

        return $this->callOpenAI($prompt);
    }

    /**
     * Analyze food from image
     */
    public function analyzeFood($imageUrl) {
        if (!$this->visionKey) {
            throw new \Exception('Vision API key not configured');
        }

        $prompt = "Analyze this food image. Identify ingredients and estimate nutritional content. Provide: dish_name, ingredients (array with estimated quantities), estimated_calories, protein_g, carbs_g, fat_g, servings.";

        return $this->callVisionAPI($imageUrl, $prompt);
    }

    /**
     * Generate meal plan
     */
    public function generateMealPlan($data) {
        $prompt = "Create a 7-day meal plan with the following requirements:\n";
        $prompt .= "- Goal: {$data['goal']}\n";
        $prompt .= "- Calories per day: " . ($data['daily_calories'] ?? 2000) . "\n";
        $prompt .= "- Dietary restrictions: " . ($data['restrictions'] ?? 'none') . "\n";
        
        $prompt .= "\nReturn JSON with days 1-7, each having breakfast, lunch, dinner, snack with recipe titles.";

        return $this->callOpenAI($prompt);
    }

    /**
     * Get cooking tips
     */
    public function getCookingTips($query) {
        $prompt = "Provide detailed cooking tips for: $query\n\nInclude: techniques, timing, common mistakes to avoid, pro tips.";
        return $this->callOpenAI($prompt);
    }

    /**
     * Call OpenAI API
     */
    private function callOpenAI($prompt) {
        $curl = curl_init();
        curl_setopt_array($curl, [
            CURLOPT_URL => 'https://api.openai.com/v1/chat/completions',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => [
                'Authorization: Bearer ' . $this->openaiKey,
                'Content-Type: application/json',
            ],
            CURLOPT_POSTFIELDS => json_encode([
                'model' => 'gpt-3.5-turbo',
                'messages' => [['role' => 'user', 'content' => $prompt]],
                'temperature' => 0.7,
            ]),
        ]);

        $response = curl_exec($curl);
        $error = curl_error($curl);
        curl_close($curl);

        if ($error) throw new \Exception('API Error: ' . $error);

        $data = json_decode($response, true);
        return $data['choices'][0]['message']['content'] ?? null;
    }

    /**
     * Call Vision API
     */
    private function callVisionAPI($imageUrl, $prompt) {
        $curl = curl_init();
        curl_setopt_array($curl, [
            CURLOPT_URL => 'https://api.openai.com/v1/chat/completions',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => [
                'Authorization: Bearer ' . $this->openaiKey,
                'Content-Type: application/json',
            ],
            CURLOPT_POSTFIELDS => json_encode([
                'model' => 'gpt-4-vision-preview',
                'messages' => [[
                    'role' => 'user',
                    'content' => [
                        ['type' => 'text', 'text' => $prompt],
                        ['type' => 'image_url', 'image_url' => ['url' => $imageUrl]],
                    ],
                ]],
            ]),
        ]);

        $response = curl_exec($curl);
        $error = curl_error($curl);
        curl_close($curl);

        if ($error) throw new \Exception('API Error: ' . $error);

        $data = json_decode($response, true);
        return $data['choices'][0]['message']['content'] ?? null;
    }
}
