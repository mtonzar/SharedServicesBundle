<?php

namespace mtonzar\SharedServicesBundle\Utils;

/**
 * Utility class to sanitize UTF-8 encoding in strings and arrays.
 * Useful for handling database error messages with special characters.
 */
class Utf8Sanitizer
{
    /**
     * Sanitize UTF-8 encoding in an array recursively.
     *
     * @param array $data The data to sanitize
     * @return array The sanitized data
     */
    public static function sanitize(array $data): array
    {
        array_walk_recursive($data, function (&$item) {
            if (is_string($item)) {
                $item = self::sanitizeString($item);
            }
        });

        return $data;
    }

    /**
     * Sanitize UTF-8 encoding in a string.
     *
     * @param string $string The string to sanitize
     * @return string The sanitized string
     */
    public static function sanitizeString(string $string): string
    {
        // Check if the string is already valid UTF-8
        if (mb_check_encoding($string, 'UTF-8')) {
            return $string;
        }

        // Try to convert from common encodings to UTF-8
        $converted = mb_convert_encoding($string, 'UTF-8', 'ISO-8859-1, Windows-1252, UTF-8');

        // Ensure all characters are valid UTF-8
        $converted = mb_convert_encoding($converted, 'UTF-8', 'UTF-8');

        // Replace any remaining invalid UTF-8 sequences (PHP 8.2+)
        if (!mb_check_encoding($converted, 'UTF-8') && function_exists('mb_scrub')) {
            $converted = mb_scrub($converted, 'UTF-8');
        }

        return $converted;
    }
}
