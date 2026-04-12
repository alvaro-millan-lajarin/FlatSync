<?php

namespace App\Validation;

class CustomRules
{
    /**
     * Check if the special code starts with "A" and is exactly 5 characters long.
     *
     * @param string $str The input value.
     * @return bool
     */
    public function check_special_code(string $str): bool
    {
        return (str_starts_with($str, 'A') && strlen($str) === 5);
    }
}