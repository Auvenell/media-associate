<?php

namespace App\Services\Sources;

// use phpseclib3\Net\SSH2 as NetSSH2;
use Illuminate\Support\Facades\Log;

class Generic
{
    public function genericSiteHandler($filename)
    {
        $filePath = '../site-data/' . $filename;
        Log::info('Starting PDF conversion', ['file_path' => $filePath]);

        if (!file_exists($filePath)) {
            Log::error('PDF file not found', ['file_path' => $filePath]);
            throw new \Exception('PDF file not found');
        }

        $curl = curl_init();

        curl_setopt_array($curl, [
            CURLOPT_URL => 'http://host.docker.internal:3030/convert',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => [
                'pdf' => new \CURLFile($filePath),
            ],
        ]);

        Log::info('Sending PDF to conversion service');
        $response = curl_exec($curl);
        $error = curl_error($curl);
        $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        curl_close($curl);

        if ($error) {
            Log::error('PDF conversion failed', ['curl_error' => $error]);
            throw new \Exception("PDF conversion failed: $error");
        } else {
            Log::info('PDF conversion response received', ['http_code' => $httpCode]);
            $outputDir = '../site-data/conversions/';
            if (!is_dir($outputDir)) {
                mkdir($outputDir, 0777, true);
            }

            $outputPath = $outputDir . pathinfo($filename, PATHINFO_FILENAME) . '.txt';
            $bytesWritten = file_put_contents($outputPath, $response);

            if ($bytesWritten === false) {
                Log::error('Failed to write converted text file', ['output_path' => $outputPath]);
                throw new \Exception('Failed to write converted text file');
            }

            Log::info('Converted text file saved', [
                'output_path' => $outputPath,
                'bytes_written' => $bytesWritten
            ]);

            if (file_exists($filePath)) {
                unlink($filePath);
                Log::info('Original PDF deleted', ['file_path' => $filePath]);
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
