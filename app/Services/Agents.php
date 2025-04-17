<?php

namespace App\Services;

use App\Services\AI\AIService;
use Illuminate\Support\Facades\Log;
use Exception;

class Agents
{
    public function retrievalAgent($text)
    {
        $agent = new AIService;
        $roleDescription = 'You are a retriever that parses out text.';
        $taskDescription = 'Parse out the text content and the publish date from' . $text;

        $query = $agent->query($roleDescription, $taskDescription);
        $response = $agent->callAPI($query);
        return $response['message']['content'] ?? null;
    }

    public function summaryAgent($data, $notes)
    {
        $agent = new AIService;
        $roleDescription = 'You are a summarizer that takes information and makes it easily digestible.';
        $taskDescription = 'Given the following: ' . $data;
        if (!empty($notes)) {
            $taskDescription .= ' and ' . '"' . $notes . '"';
        }
        $taskDescription .= ' produce a paragraph or sentence to summarize the information and make sure the date is mentioned at the end. Don\'t talk to the user directly.';

        $query = $agent->query($roleDescription, $taskDescription);
        $response = $agent->callAPI($query);
        // Log::info('Ollama summaryAgent raw response', ['response' => $response]);
        return $response['message']['content'] ?? null;
    }
}
