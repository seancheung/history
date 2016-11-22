<?php

namespace Panoscape\History;

trait HasOperations
{
    /**
     * Get all of the agent's operations.
     */
    public function operations()
    {
        return $this->morphMany(History::class, 'user');
    }
}
