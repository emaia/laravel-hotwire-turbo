<?php

namespace Emaia\LaravelHotwireTurbo\Enums;

enum Action: string
{
    case APPEND = 'append';
    case PREPEND = 'prepend';
    case REPLACE = 'replace';
    case UPDATE = 'update';
    case REMOVE = 'remove';
    case AFTER = 'after';
    case BEFORE = 'before';
    case MORPH = 'morph';
    case REFRESH = 'refresh';
}
