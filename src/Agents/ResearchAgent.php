<?php

namespace App\Agents;

use NeuronAI\Agent\Agent;
use NeuronAI\Providers\AIProviderInterface;
use NeuronAI\Providers\Anthropic\Anthropic;
use NeuronAI\Providers\Gemini\Gemini;
use NeuronAI\Providers\OpenAI\OpenAI;
use NeuronAI\Providers\Deepseek\Deepseek;

class ResearchAgent extends Agent
{
    protected function provider(): AIProviderInterface
    {
        return new Deepseek(
            $_ENV['DEEPSEEK_API_KEY'],
            'deepseek-v4-flash'
        );
        // if (isset($_ENV['ANTHROPIC_API_KEY'])) {
        //     return new Anthropic(
        //         $_ENV['ANTHROPIC_API_KEY'],
        //         'claude-3-7-sonnet-latest'
        //     );
        // }

        // if (isset($_ENV['OPENAI_API_KEY'])) {
        //     return new OpenAI(
        //         $_ENV['OPENAI_API_KEY'],
        //         'gpt-3.5-turbo'
        //     );
        // }

        // if (isset($_ENV['GEMINI_API_KEY'])) {
        //     return new Gemini(
        //         $_ENV['GEMINI_API_KEY'],
        //         'gemini-2.0-flash'
        //     );
        // }

        throw new \Exception('You need a valid API key for Anthropic, OpenAI, or Gemini.');
    }
}