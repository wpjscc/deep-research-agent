<?php

namespace App\Nodes;

use App\Agents\ResearchAgent;
use App\Events\ProgressEvent;
use App\Events\FormattingReportEvent;
use App\Events\SectionGenerationEvent;
use App\Prompts;
use App\SearchWorkflow;
use NeuronAI\Chat\Messages\AssistantMessage;
use NeuronAI\Chat\Messages\Stream\Chunks\TextChunk;
use NeuronAI\Chat\Messages\UserMessage;
use NeuronAI\Exceptions\WorkflowException;
use NeuronAI\Workflow\Node;
use NeuronAI\Workflow\Interrupt\WorkflowInterrupt;
use NeuronAI\Workflow\WorkflowState;

/**
 * For multiple sections this node will be called multiple times.
 */
class GenerateSectionContent extends Node
{
    /**
     * @throws \Throwable
     * @throws WorkflowInterrupt
     * @throws WorkflowException
     */
    public function __invoke(SectionGenerationEvent $event, WorkflowState $state): \Generator|SectionGenerationEvent|FormattingReportEvent
    {
        $index = $state->get('current_section', 0);

        if ($index >= \count($event->plan->sections)) {
            // All sections have been generated, move forward
            return new FormattingReportEvent($event->plan);
        }

        $handler = SearchWorkflow::make(state: new WorkflowState(['query' => $event->plan->sections[$index]->description]))->init();
        foreach ($handler->events() as $streamedEvent) {
            yield $streamedEvent;
        }
        $searchState = $handler->run();

        $prompt = \str_replace('{section_topic}', $event->plan->sections[$index]->description, Prompts::SECTION_WRITER_INSTRUCTIONS);
        $prompt = \str_replace('{context}', \implode("\n", $searchState->get('results')), $prompt);

        yield new ProgressEvent("\n\n========== Generating content for section: {$event->plan->sections[$index]->name} ==========\n\n");

        $handler = ResearchAgent::make()->stream(new UserMessage($prompt));

        foreach ($handler->events() as $chunk) {
            if ($chunk instanceof TextChunk) {
                yield new ProgressEvent($chunk->content);
            }
        }

        /** @var AssistantMessage $message */
        $message = $handler->getMessage();

        $event->plan->sections[$index]->content = $message->getContent();

        // Loop back to this node until all sections are processed
        $state->set('current_section', $index + 1);
        return $event;
    }
}