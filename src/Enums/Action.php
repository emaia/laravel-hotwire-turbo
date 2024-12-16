<?php

namespace Emaia\LaravelTurbo\Enums;

enum Action: string
{
    case APPEND = 'append';
    case PREPEND = 'prepend';
    case REPLACE = 'replace';
    case UPDATE = 'update';
    case REMOVE = 'remove';
}
