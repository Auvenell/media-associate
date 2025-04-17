<?php

namespace App\Services\Sources;

// use phpseclib3\Net\SSH2 as NetSSH2;

class Generic
{
    public function genericSiteHandler($filename)
    {
        $filePath = '../site-data/' . $filename;

        $curl = curl_init();

        curl_setopt_array($curl, [
            CURLOPT_URL => 'http://host.docker.internal:3030/convert',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => [
                'pdf' => new \CURLFile($filePath),
            ],
        ]);

        $response = curl_exec($curl);
        $error = curl_error($curl);
        curl_close($curl);

        if ($error) {
            echo "cURL Error: $error";
        } else {
            $outputDir = '../site-data/conversions/';
            if (!is_dir($outputDir)) {
                mkdir($outputDir, 0777, true);
            }

            $outputPath = $outputDir . pathinfo($filename, PATHINFO_FILENAME) . '.txt';
            file_put_contents($outputPath, $response);
            if (file_exists($filePath)) {
                unlink($filePath);
            }
            return $outputPath;
        }
    }

    function getUrlFromPdfText($article)
    {
        preg_match_all('/^\s*:\s*\n\s*\n(https?:\/\/[^\s]+)/m', $article, $matches); // search for url pattern in pdf text
        $unique = array_unique($matches[1]);
        return array_values($unique); // re-index array
    }
}
