<?php

namespace App\Services;

use App\Services\AI\AIService;
use Illuminate\Support\Facades\Log;
use Exception;

class Agents
{
    private const DEFAULT_MODEL = 'qwen3-30b-a3b-dwq';

    private function cleanAIResponse($response)
    {
        if (!$response) {
            return null;
        }

        // Remove <think> sections and any extra newlines
        $response = preg_replace('/<think>.*?<\/think>\s*/s', '', $response);
        $response = trim($response);

        return $response;
    }

    public function retrievalAgent($text)
    {
        $agent = new AIService;
        $roleDescription = 'You are a retriever that parses out text.';
        $taskDescription = '/no_think Parse out the text content and the publish date from' . $text;

        $query = $agent->query($roleDescription, $taskDescription, self::DEFAULT_MODEL);
        $response = $agent->callAPI($query);
        return $this->cleanAIResponse($response['message']['content'] ?? null);
    }

    public function summaryAgent($data, $notes)
    {
        $agent = new AIService;
        $roleDescription = 'You are a summarizer that takes information and makes it easily digestible.';
        $taskDescription = '/no_think Given the following: ' . $data;
        if (!empty($notes)) {
            $taskDescription .= ' and ' . '"' . $notes . '"';
        }
        $taskDescription .= ' produce a paragraph to summarize the information and make sure the date is mentioned at the end. Don\'t talk to the user directly.';

        Log::info('Preparing summary agent query', [
            'data_length' => strlen($data),
            'has_notes' => !empty($notes)
        ]);

        $query = $agent->query($roleDescription, $taskDescription, self::DEFAULT_MODEL);
        $response = $agent->callAPI($query);
        Log::info('Summary agent response', [
            'response' => $response,
            'has_content' => isset($response['message']['content'])
        ]);

        return $this->cleanAIResponse($response['message']['content'] ?? null);
    }

    public function titleAgent($text)
    {
        $agent = new AIService;
        $roleDescription = 'You are an expert at finding details in text articles.';
        $taskDescription = '/no_think Find the article\'s title by finding the author. The preceding text is usually the title. Return just the title in double quotes.' . $text;

        $query = $agent->query($roleDescription, $taskDescription, self::DEFAULT_MODEL);
        $response = $agent->callAPI($query);

        return $this->cleanAIResponse($response['message']['content'] ?? null);
    }

    public function evidenceAgent($text)
    {
        $agent = new AIService;
        $roleDescription = 'You are an expert at finding supporting evidence.
        Your only job is to read an article and return quotes from the article that support and
        reinforce the main idea. You always return properly formatted paragraphs with a line
        between each piece of evidence.';

        $taskDescription = '/no_think Return a list of supporting evidence from the following article: ' . $text;

        $query = $agent->query($roleDescription, $taskDescription, self::DEFAULT_MODEL);
        $response = $agent->callAPI($query);

        return $this->cleanAIResponse($response['message']['content'] ?? null);
    }

    public function creativeTitleAgent($text)
    {
        $agent = new AIService;
        $roleDescription = 'You are an expert at developing informative but creative titles.
        Your only job is to read an article, its title, and then return a spin on the title that includes
        any numbers or important information. It\'s okay for your title to be similar to the article\'s original.
        You only return a single title in double quotes without any additional text.';

        $taskDescription = '/no_think Return a fitting title that takes a unconventional spin on the article: ' . $text;

        $query = $agent->query($roleDescription, $taskDescription, self::DEFAULT_MODEL);
        $response = $agent->callAPI($query);

        return $this->cleanAIResponse($response['message']['content'] ?? null);
    }
}
