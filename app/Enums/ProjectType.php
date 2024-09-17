<?php

namespace App\Enums;

use InvalidArgumentException;

/***
 * Enums for project type:
 * @INTERNAL IS FOR INTERNAL PROJECTS
 * @EXTERNAL IS FOR EXTERNAL PROJECTS
 *
 * @var STRING
 */

enum ProjectType: string
{
    case INTERNAL = 'internal';
    case EXTERNAL = 'external';

    public function title(): string
    {
        return ucfirst($this->value);
    }

    public static function isValidType(string $type): bool
    {
        return in_array(strtolower($type), [
            self::INTERNAL->value,
            self::EXTERNAL->value,
        ]);
    }

    public static function validateType(string $type): void
    {
        if (!self::isValidType($type)) {
            throw new InvalidArgumentException("Invalid project type: $type");
        }
    }
}
