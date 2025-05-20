<?php

namespace ServerShellBundle\Message;

class ScriptExecutionMessage
{
    public function __construct(
        private readonly int $executionId,
    ) {
    }

    public function getExecutionId(): int
    {
        return $this->executionId;
    }
}
