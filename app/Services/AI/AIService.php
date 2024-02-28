<?php

namespace App\Services\AI;

use App\Models\Inbounds;
use App\Services\Sources\TwitterX;
use Illuminate\Support\Facades\DB;
use Exception;

class AIService
{
    public function callAPI($query)
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

    public function query($roleDescription, $taskDescription)
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
