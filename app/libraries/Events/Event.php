<?php

namespace NovaFlow\Core\Events;

/**
 * Base Abstract Event Class
 */
abstract class Event
{
    /**
     * Whether propagation has been stopped
     */
    protected bool $propagationStopped = false;

    /**
     * Stop the propagation of the event to subsequent listeners
     */
    public function stopPropagation(): static
    {
        $this->propagationStopped = true;
        return $this;
    }

    /**
     * Determine if the event's propagation was stopped
     */
    public function isPropagationStopped(): bool
    {
        return $this->propagationStopped;
    }
}
