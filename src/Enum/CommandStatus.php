<?php

namespace ServerShellBundle\Enum;

enum CommandStatus: string
{
    case PENDING = 'pending';
    case RUNNING = 'running';
    case COMPLETED = 'completed';
    case FAILED = 'failed';
    case TIMEOUT = 'timeout';
    case CANCELED = 'canceled';
}
