<?php

namespace App\Enums;

/***
 * @SP IS FOR SPECIALIZATION
 * @DP IS FOR DEPARTMENT
 * 
 * @var STRING
 */

enum UserGroup: string
{
    case SP = 'specialization';
    case DP  = 'department';


    public function title(): string
    {
        return ucfirst($this->value);
    }

    /**
     * Search for a model by its value.
     *
     * @param string $value The value to search for
     * @return ?UserGroup The role if found, null otherwise
     */
    public static function searchByValue(string $value): ?UserGroup
    {
        $lowercaseValue = strtolower($value);
        foreach (self::cases() as $case) {
            if (strtolower($case->value) === $lowercaseValue) {
                return $case;
            }
        }
        return null;
    }
}
