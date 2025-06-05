<?php

namespace App\Services\AI;

use App\Models\Inbounds;
use App\Services\Sources\TwitterX;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Exception;

class AIService
{
    const PROVIDER_OLLAMA = 'ollama';
    const PROVIDER_LM_STUDIO = 'lm_studio';

    private function getProvider()
    {
        return env('AI_PROVIDER', self::PROVIDER_OLLAMA);
    }

    private function getProviderUrl()
    {
        $provider = $this->getProvider();

        return match ($provider) {
            self::PROVIDER_OLLAMA => env('OLLAMA_API_URL', 'http://host.docker.internal:11434/api/chat'),
            self::PROVIDER_LM_STUDIO => env('LM_STUDIO_API_URL', 'http://host.docker.internal:1234/v1/chat/completions'),
            default => throw new Exception("Unsupported AI provider: {$provider}")
        };
    }

    private function formatRequestForProvider($query)
    {
        $provider = $this->getProvider();

        // LM Studio expects OpenAI-compatible format
        if ($provider === self::PROVIDER_LM_STUDIO) {
            return [
                'model' => $query['model'],
                'messages' => $query['messages'],
                'temperature' => $query['temperature'],
                'max_tokens' => $query['max_tokens'] === -1 ? null : $query['max_tokens'],
                'stream' => $query['stream']
            ];
        }

        // Ollama format remains unchanged
        return $query;
    }

    private function parseResponse($response, $provider)
    {
        if ($provider === self::PROVIDER_LM_STUDIO) {
            $decoded = json_decode($response, true);
            if (isset($decoded['choices'][0]['message'])) {
                return [
                    'message' => $decoded['choices'][0]['message']
                ];
            }
        }

        // Ollama response format remains unchanged
        return json_decode($response, true);
    }

    public function callAPI($query)
    {
        $provider = $this->getProvider();
        $url = $this->getProviderUrl();
        $formattedQuery = $this->formatRequestForProvider($query);

        Log::info("Attempting AI provider call", [
            'provider' => $provider,
            'url' => $url
        ]);

        $ch = curl_init($url);

        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($formattedQuery));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        $response = curl_exec($ch);

        if (curl_errno($ch)) {
            Log::error("AI Provider ({$provider}) cURL Error: " . curl_error($ch));
            curl_close($ch);
            return null;
        }

        curl_close($ch);

        try {
            $parsed = $this->parseResponse($response, $provider);
            if (!$parsed) {
                Log::error("AI Provider ({$provider}) Response Parse Error", [
                    'response' => $response
                ]);
                return null;
            }
            return $parsed;
        } catch (Exception $e) {
            Log::error("AI Provider ({$provider}) Response Processing Error: " . $e->getMessage(), [
                'response' => $response
            ]);
            return null;
        }
    }

    public function query($roleDescription, $taskDescription, $model = 'qwen3:30b-a3b-q4_K_M')
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
