<?php

namespace App\Http\Enums;

enum LogActionEnum: string
{
    case CREATE = 'create';
    case UPDATE = 'update';
    case DELETE = 'delete';
    case SYNC = 'sync';
}
