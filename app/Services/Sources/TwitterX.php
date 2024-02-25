<?php

namespace App\Services\Sources;

use App\Models\Inbounds;
use App\Http\Controllers\InboundsController;
use Illuminate\Support\Facades\DB;
use Exception;

class TwitterX
{
    public function handleTwitterX($url)
    {
        // URL for Twitter oEmbed API
        $oembed_url = 'https://publish.twitter.com/oembed?url=' . urlencode($url);

        try {
            // Send a GET request to the oEmbed API
            $response = file_get_contents($oembed_url);

            // Check for successful response
            if ($response !== false) {
                // Decode JSON response
                $tweet_info = json_decode($response, true);

                // Extract relevant information from the JSON response
                return [
                    'text' => $tweet_info['html'],
                    'author' => $tweet_info['author_name']
                ];
            } else {
                echo 'Error: Unable to fetch tweet';
                return [];
            }
        } catch (Exception $e) {
            echo 'Error fetching tweet: ' . $e->getMessage();
            return [];
        }
    }
}
