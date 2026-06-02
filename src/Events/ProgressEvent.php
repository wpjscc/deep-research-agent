<?php

namespace App\Events;

use NeuronAI\Workflow\Events\Event;

class ProgressEvent implements Event
{
    public function __construct(public string $message)
    {
    }
}