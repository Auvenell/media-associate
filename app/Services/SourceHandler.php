<?php

namespace App\Services;

use App\Models\Inbounds;
use App\Services\Sources\TwitterX;
use Illuminate\Support\Facades\DB;
use Exception;

class SourceHandler
{
    public function retrievalAgent($text)
    {
        $roleDescription = 'You are a retriever that parses out text.';
        $taskDescription = 'Parse out the text content and the publish date from' . $text;

        $query = $this->query($roleDescription, $taskDescription);
        $response = $this->callAPI($query);
        return $response;
    }

    public function summaryAgent($data, $notes)
    {
        $roleDescription = 'You are a summarizer that takes information and makes it easily digestible.';
        $taskDescription = 'Given the following: ' . $data;
        if(!empty($notes)){
            $taskDescription .= ' and ' . '"' . $notes . '"';
        }
        $taskDescription .= ' produce a paragraph or sentence to summarize the information and make sure the date is mentioned at the end';

        $query = $this->query($roleDescription, $taskDescription);
        $response = $this->callAPI($query);
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

    private function callAPI($query)
    {
        $url = env('AI_API_URL', 'http://localhost:1234/v1/chat/completions');
        // Set up cURL
        $ch = curl_init($url);

        // Set cURL options
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($query));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        // Execute the request
        $response = curl_exec($ch);

        // Check for errors
        if (curl_errno($ch)) {
            echo 'Error: ' . curl_error($ch);
        }

        // Close cURL resource
        curl_close($ch);

        return response()->json($response);
    }
}
