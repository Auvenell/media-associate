<?php

namespace App\Services;

use App\Services\AI\AIService;
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
        return $response;
    }

    public function summaryAgent($data, $notes)
    {
        $agent = new AIService;
        $roleDescription = 'You are a summarizer that takes information and makes it easily digestible.';
        $taskDescription = 'Given the following: ' . $data;
        if(!empty($notes)){
            $taskDescription .= ' and ' . '"' . $notes . '"';
        }
        $taskDescription .= ' produce a paragraph or sentence to summarize the information and make sure the date is mentioned at the end';

        $query = $agent->query($roleDescription, $taskDescription);
        $response = $agent->callAPI($query);
        return $response;
    }

    public function genericRetrievalAgent($text)
    {
        $agent = new AIService;
        $roleDescription = 'Below is an instruction that describes a task. Write a response that appropriately completes the request.';
        $taskDescription = '"Get the article from this document:

' . $text . '"';
        $query = $agent->query($roleDescription, $taskDescription);
        $response = $agent->callAPI($query);
        return $response;
    }
}
