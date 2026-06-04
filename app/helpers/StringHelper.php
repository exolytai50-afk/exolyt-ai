<?php
/**
 * String Helper
 */

namespace App\Helpers;

class StringHelper {
    public static function slugify($text) {
        $text = preg_replace('/[^\w\s-]/', '', $text);
        $text = preg_replace('/[\s_]+/', '-', $text);
        return strtolower(trim($text, '-'));
    }

    public static function truncate($text, $length = 100) {
        if (strlen($text) > $length) {
            return substr($text, 0, $length) . '...';
        }
        return $text;
    }

    public static function sanitize($text) {
        return htmlspecialchars($text, ENT_QUOTES, 'UTF-8');
    }

    public static function escape($text) {
        return addslashes($text);
    }
}
