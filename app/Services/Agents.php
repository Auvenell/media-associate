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

        $query = $agent->query($roleDescription, $taskDescription, 'qwen2.5:14b-instruct-q8_0');
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

        $query = $agent->query($roleDescription, $taskDescription, 'qwen2.5:14b-instruct-q8_0');
        $response = $agent->callAPI($query);
        // Log::info('Ollama summaryAgent raw response', ['response' => $response]);
        return $response['message']['content'] ?? null;
    }

    public function titleAgent($text)
    {
        $agent = new AIService;
        $roleDescription = 'You are a title extractor. Your only job is to read a passage and return the article\'s title as a string in double quotes.';
        $taskDescription = 'Extract only the title from the following article: ' . $text;

        $query = $agent->query($roleDescription, $taskDescription, 'qwen2.5-coder:1.5b');
        $response = $agent->callAPI($query);

        return $response['message']['content'] ?? null;
    }

    public function evidenceAgent($text)
    {
        $agent = new AIService;
        $roleDescription = 'You are an expert at finding supporting evidence.
        Your only job is to read an article and return quotes from the article that support and
        reinforce the main idea. You always return properly formatted paragraphs with a line
        between each piece of evidence.';

        $taskDescription = 'Return a list of supporting evidence from the following article: ' . $text;

        $query = $agent->query($roleDescription, $taskDescription, 'qwen2.5:14b-instruct-q8_0');
        $response = $agent->callAPI($query);

        return $response['message']['content'] ?? null;
    }

    public function creativeTitleAgent($text)
    {
        $agent = new AIService;
        $roleDescription = 'You are an expert at developing informative but creative titles.
        Your only job is to read an article, its title, and then return a spin on the title that includes
        any numbers or important information. Its okay for your title to be similar to the article\'s original.
        You only return a single title in double quotes without any additional text.';

        $taskDescription = 'Return a fitting title that takes a unconventional spin on the article: ' . $text;

        $query = $agent->query($roleDescription, $taskDescription, 'qwen2.5:14b-instruct-q8_0');
        $response = $agent->callAPI($query);

        return $response['message']['content'] ?? null;
    }
}
