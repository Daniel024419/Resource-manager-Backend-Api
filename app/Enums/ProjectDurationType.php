<?php

namespace App\Enums;

/***
 * Enums for project duration type:
 * @SHORT IS FOR SHORT PROJECTS DURATION
 * @NORMAL IS FOR NORMAL PROJECTS DURATION
 * @LONG IS FOR LONG PROJECTS DURATION
 *
 * @var STRING
 */

enum ProjectDurationType: string
{
    case SHORT = 'short';
    case LONG   = 'long';

    public function title(): string
    {
        return ucfirst($this->value);
    }

    public static function getRatingRange(string $durationType): array
    {
        switch ($durationType) {
            case self::SHORT->value:
                return [3, 5]; 
            case self::LONG->value:
                return [1, 5]; 
            default:
                return [0, 0]; 
        }
    }
    
}
