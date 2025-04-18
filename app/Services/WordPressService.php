<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class WordPressService
{
    protected $username = 'auvenell';
    protected $appPassword = 'H2r6 227s boSI yY4V GM68 fS6H';
    protected $apiUrl = 'https://wealthworks.io/wp-json/wp/v2/posts';

    // dev version
    // protected $username = "admin";
    // protected $appPassword = "7EUb 9XtB ldLj VhRw 4BbV 4RMN";
    // protected $apiUrl = "http://wealthworks.local/wp-json/wp/v2/posts";

    public function createPost($title, $content, $categories = [], $meta = [])
    {
        $response = Http::withHeaders([
            'Authorization' => 'Basic ' . base64_encode($this->username . ':' . $this->appPassword),
            'Content-Type' => 'application/json',
        ])->post($this->apiUrl, [
            'title' => $title,
            'content' => $content,
            'status' => 'draft',
            'categories' => $categories,
            'meta' => $meta,
        ]);

        return $response->json();
    }
}
