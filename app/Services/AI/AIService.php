<?php

namespace App\Services\AI;

use App\Models\Inbounds;
use App\Services\Sources\TwitterX;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Exception;

class AIService
{
    public function callAPI($query)
    {
        $url = env('AI_API_URL', 'http://host.docker.internal:11434/api/chat'); // access Ollama from Docker
        $ch = curl_init($url);

        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($query));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        $response = curl_exec($ch);

        if (curl_errno($ch)) {
            Log::error('Ollama cURL Error: ' . curl_error($ch));
            curl_close($ch);
            return null;
        }

        curl_close($ch);

        $decoded = json_decode($response, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            Log::error('Ollama JSON Decode Error: ' . json_last_error_msg());
            return null;
        }

        return $decoded;
    }

    public function query($roleDescription, $taskDescription, $model = 'qwen2.5:14b-instruct-q8_0')
    {
        $aiQuery = array(
            'model' => $model,
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
