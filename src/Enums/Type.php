<?php

declare(strict_types=1);

namespace Orkhanahmadov\ModelSettings\Enums;

enum Type: string
{
    case STRING = 'string';
    case INT = 'int';
    case JSON = 'json';
    case ARRAY = 'array';
    case BOOLEAN = 'bool';
    case DATETIME = 'immutable_datetime';
}
