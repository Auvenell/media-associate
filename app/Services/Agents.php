<?php

namespace App\Services;

use App\Models\Inbounds;
use App\Services\Sources\TwitterX;
use App\Services\AI\AIService;
use Illuminate\Support\Facades\DB;
use Exception;

class Agents
{
    public function retrievalAgent($text)
    {
        $agent = new AIService;
        $roleDescription = 'You are a retriever that parses out text.';
        $taskDescription = 'Parse out the text content and the publish date from' . $text;

        $query = $this->query($roleDescription, $taskDescription);
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

        $query = $this->query($roleDescription, $taskDescription);
        $response = $agent->callAPI($query);
        return $response;
    }

    private function query($roleDescription, $taskDescription)
    {
        $aiQuery = array(
            'messages' => array(
                array(
                    'role' => 'system',
                    'content' => $roleDescription
                ),
                array(
                    'role' => 'user',
                    'content' => $taskDescription
                )
            ),
            'temperature' => 0.2,
            'max_tokens' => -1,
            'stream' => false
        );

        return $aiQuery;
    }
}
