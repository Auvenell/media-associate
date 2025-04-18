<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Models\Inbounds;
use App\Services\Agents;
use App\Services\Sources\Generic;
use App\Services\Sources\TwitterX;
use App\Services\Sources\SiteList;
use Exception;
use Illuminate\Http\JsonResponse;

class InboundsController extends Controller
{
    public function receiveInbounds(Request $request)
    {
        $agent = new Agents;
        $inbounds = new Inbounds;

        if ($request->hasFile('pdf') && $request->file('pdf')->isValid()) {
            $pdf = $request->file('pdf');
            $filename = time() . '-' . $pdf->getClientOriginalName();
            $destinationPath = '../site-data/';
            $pdf->move($destinationPath, $filename);
        }

        $inbounds->notes = $request->notes ? $request->notes : '';

        if (empty($inbounds->source)) {
            $inbounds->source = parse_url(json_decode($inbounds->url), PHP_URL_HOST);
        }
        $inbounds->user_id = $request->user_id ?? 1;

        // $inboundId = $this->getLastInbound();

        if (in_array($inbounds->source, SiteList::$twitterUrls)) { // specific handling for Twitter URLs
            $twitterXHandler = new TwitterX;
            $tweetRaw = $twitterXHandler->handleTwitterX($request->url);
            $content = $tweetRaw['text'];
            $author = $tweetRaw['author'];

            $tweetData = $agent->retrievalAgent($content);
            $tweetContent = response()->json([$tweetData]);
            $tweetContent = json_decode($tweetContent->getContent(), true)[0]['original'];
            $tweetContent = json_decode($tweetContent, true)['choices'][0]['message']['content'];

            $summary = $agent->summaryAgent($tweetContent, $inbounds->notes);
            $summaryContent = response()->json([$summary]);
            $summaryContent = json_decode($summaryContent->getContent(), true)[0]['original'];
            $summaryContent = json_decode($summaryContent, true)['choices'][0]['message']['content'];

            $inbounds->summary = $summaryContent;
        }

        if (!in_array($inbounds->source, SiteList::$twitterUrls)) { // all other sites
            $genericSite = new Generic;
            $convertedFilePath = $genericSite->genericSiteHandler($filename); // convert PDF & get text file path
            $inbounds->text_path = basename($convertedFilePath);  // store text file path
            $articleContent = file_get_contents($convertedFilePath); // get article content
            $url = $genericSite->getUrlFromPdfText($articleContent); // get article url
            $inbounds->url = $url[0] ?? null;
            $inbounds->source = parse_url($inbounds->url, PHP_URL_HOST); // get article publisher
            // exit();

            $summaryContent = $agent->summaryAgent($articleContent, $inbounds->notes);
            $inbounds->summary = $summaryContent; // attach summary to model
            if (empty($inbounds->summary)) { // blank value for db in case of failure
                $inbounds->summary = null;
            }

            $inbounds->save(); // save to db
            return response()->json($summaryContent, 201);
        }
    }

    public function showAllInbounds()
    {
        $inbounds = Inbounds::all();
        return response()->json($inbounds);
    }

    public function showInbound($id)
    {
        $inbounds = Inbounds::find($id);
        if (!empty($inbounds)) {
            return response()->json($inbounds);
        } else {
            return response()->json(['message' => 'Post Not Found'], 404);
        }
    }

    public function getLastInbound()
    {
        $id = Inbounds::orderBy('id', 'DESC')->first();
        return isset($id) ? $id : null;
    }

    public function updateInbound(Request $request, $id)
    {
        $inbounds = Inbounds::find($id);
        if (!empty($inbounds)) {
            $inbounds->url = $request->url ?? $inbounds->url;
            $inbounds->notes = $request->notes ?? $inbounds->notes;
            $inbounds->summary = $request->summary ?? $inbounds->summary;
            $inbounds->save();
            return response()->json(['message' => 'Post Updated'], 200);
        } else {
            return response()->json(['message' => 'Post Not Found'], 404);
        }
    }

    public function removeInbound($id)
    {
        $inbound = Inbounds::find($id);
        if (!empty($inbound)) {
            // Delete the text file if it exists
            $filePath = '../site-data/conversions/' . $inbound->text_path;
            if (file_exists($filePath)) {
                unlink($filePath);
            }

            // Delete the database record
            $inbound->delete();
            return response()->json(['message' => 'Post Destroyed'], 200);
        } else {
            return response()->json(['message' => 'Post Not Found'], 404);
        }
    }

    public function regenerateSummary($id): JsonResponse
    {
        $inbound = Inbounds::find($id);

        if (!$inbound || !$inbound->text_path) {
            return response()->json(['message' => 'Invalid inbound or missing text path'], 400);
        }

        $filePath = '../site-data/conversions/' . $inbound->text_path;

        if (!file_exists($filePath)) {
            return response()->json(['message' => 'Text file not found'], 404);
        }

        try {
            $agent = new Agents;
            $articleContent = file_get_contents($filePath);
            $summary = $agent->summaryAgent($articleContent, $inbound->notes ?? '');

            $inbound->summary = $summary;
            $inbound->save();

            return response()->json(['summary' => $summary], 200);
        } catch (Exception $e) {
            Log::error('Regenerate summary failed', ['error' => $e->getMessage()]);
            return response()->json(['message' => 'Error regenerating summary'], 500);
        }
    }
}
