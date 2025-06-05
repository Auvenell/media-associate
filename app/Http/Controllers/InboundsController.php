<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Models\Inbounds;
use App\Models\PostMetadata;
use App\Services\Agents;
use App\Services\Sources\Generic;
use App\Services\Sources\TwitterX;
use App\Services\Sources\SiteList;
use App\Services\WordPressService;
use Exception;
use Illuminate\Http\JsonResponse;

class InboundsController extends Controller
{
    public function receiveInbounds(Request $request)
    {
        Log::info('Received inbound request', [
            'has_pdf' => $request->hasFile('pdf'),
            'has_url' => $request->has('url'),
            'request_data' => $request->all()
        ]);

        $agent = new Agents;
        $inbounds = new Inbounds;

        if ($request->hasFile('pdf') && $request->file('pdf')->isValid()) {
            Log::info('Processing PDF file');
            $pdf = $request->file('pdf');
            $filename = time() . '-' . $pdf->getClientOriginalName();
            $destinationPath = '../site-data/';
            try {
                $pdf->move($destinationPath, $filename);
                Log::info('PDF saved', ['filename' => $filename]);
            } catch (Exception $e) {
                Log::error('Failed to save PDF', ['error' => $e->getMessage()]);
                return response()->json(['error' => 'Failed to save PDF'], 500);
            }
        }

        // Set URL from request or leave empty for PDF processing
        $inbounds->url = $request->url ?? null;
        $inbounds->notes = $request->notes ? $request->notes : '';

        // Set source based on URL if available
        if ($inbounds->url) {
            $inbounds->source = parse_url($inbounds->url, PHP_URL_HOST);
        }

        $inbounds->user_id = $request->user_id ?? 1;

        if (!in_array($inbounds->source, SiteList::$twitterUrls)) { // all other sites
            try {
                $genericSite = new Generic;
                $convertedFilePath = $genericSite->genericSiteHandler($filename); // convert PDF & get text file path
                Log::info('File converted', ['converted_path' => $convertedFilePath]);

                $inbounds->text_path = basename($convertedFilePath);  // store text file path
                $articleContent = file_get_contents($convertedFilePath); // get article content
                Log::info('Article content loaded', [
                    'content_length' => strlen($articleContent),
                    'file_path' => $convertedFilePath
                ]);

                // Only try to extract URL from PDF if none was provided in the request
                if (!$inbounds->url) {
                    $extractedUrl = $genericSite->getUrlFromPdfText($articleContent);
                    $inbounds->url = $extractedUrl[0] ?? null;
                    $inbounds->source = $inbounds->url ? parse_url($inbounds->url, PHP_URL_HOST) : null;
                }

                $summaryContent = $agent->summaryAgent($articleContent, $inbounds->notes);
                Log::info('Summary generation attempt', [
                    'summary_content' => $summaryContent,
                    'notes' => $inbounds->notes
                ]);

                $inbounds->summary = $summaryContent; // attach summary to model
                if (empty($inbounds->summary)) { // blank value for db in case of failure
                    $inbounds->summary = null;
                }

                // Generate creative title
                $creativeTitle = $agent->creativeTitleAgent($articleContent);
                $inbounds->post_title = $creativeTitle ? trim($creativeTitle, '"') : '';

                try {
                    Log::info('Attempting to save inbound', [
                        'url' => $inbounds->url,
                        'source' => $inbounds->source,
                        'has_summary' => !empty($inbounds->summary),
                        'has_title' => !empty($inbounds->post_title)
                    ]);

                    $inbounds->save(); // save to db

                    // Create initial metadata
                    $inbounds->metadata()->create([
                        'categories' => [],
                        'sentiment' => 'neutral',
                        'market_mover' => 'no',
                    ]);

                    // Get title and evidence for sources table
                    $title = $agent->titleAgent($articleContent);
                    $evidence = $agent->evidenceAgent($articleContent);

                    // Create source record
                    $inbounds->sources()->create([
                        'url' => $inbounds->url,
                        'title' => $title ? trim($title, '"') : '',
                        'excerpt' => $evidence ?: ''
                    ]);

                    Log::info('Inbound saved successfully', ['inbound_id' => $inbounds->id]);

                    return response()->json([
                        'summary' => $summaryContent,
                        'post_title' => $inbounds->post_title,
                        'metadata' => $inbounds->metadata
                    ], 201);
                } catch (Exception $e) {
                    Log::error('Failed to save inbound', [
                        'error' => $e->getMessage(),
                        'trace' => $e->getTraceAsString()
                    ]);
                    return response()->json(['error' => 'Failed to save inbound'], 500);
                }
            } catch (Exception $e) {
                Log::error('Error processing article', [
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString()
                ]);
                return response()->json(['error' => 'Error processing article'], 500);
            }
        }
    }

    public function showAllInbounds()
    {
        $inbounds = Inbounds::with('metadata')->get();
        return response()->json($inbounds);
    }

    public function showInbound($id)
    {
        $inbound = Inbounds::with('metadata')->find($id);
        if (!empty($inbound)) {
            return response()->json($inbound);
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
        $inbound = Inbounds::find($id);
        if (!empty($inbound)) {
            $inbound->url = $request->url ?? $inbound->url;
            $inbound->notes = $request->notes ?? $inbound->notes;
            $inbound->summary = $request->summary ?? $inbound->summary;
            $inbound->post_title = $request->post_title ?? $inbound->post_title;
            $inbound->source = $inbound->url ? parse_url($inbound->url, PHP_URL_HOST) : null;

            // Update or create metadata
            if ($request->has('metadata')) {
                $metadata = $request->input('metadata');
                $inbound->metadata()->updateOrCreate(
                    ['inbound_id' => $inbound->id],
                    [
                        'categories' => $metadata['categories'] ?? [],
                        'sentiment' => $metadata['sentiment'] ?? 'neutral',
                        'market_mover' => $metadata['market_mover'] ?? 'no',
                    ]
                );
            }

            $inbound->save();
            return response()->json([
                'message' => 'Post Updated',
                'post_title' => $inbound->post_title,
                'metadata' => $inbound->metadata
            ], 200);
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

    public function publishToWordPress(Request $request, WordPressService $wordpress, $id)
    {
        $inbound = Inbounds::with('sources')->find($id);

        if (!$inbound) {
            return response()->json(['message' => 'Inbound not found'], 404);
        }

        $title = $inbound->post_title ?: ($inbound->source ?? 'Untitled Source');
        $content = $inbound->summary ?? 'No summary available';

        $categories = $request->input('categories', [29, 30]);
        $metaInput = $request->input('meta', []);
        $meta = [
            'sentiment' => $metaInput['sentiment'] ?? 'neutral',
            'market_mover' => $metaInput['market_mover'] ?? 'unknown',
            'sources' => [
                [
                    'title' => $inbound->sources?->title ?? $inbound->source,
                    'url' => $inbound->sources?->url ?? $inbound->url,
                    'excerpt' => $inbound->sources?->excerpt ?? ($inbound->notes ?? ''),
                ],
            ],
        ];

        $response = $wordpress->createPost($title, $content, $categories, $meta);
        Log::info('WordPress draft response', $response);
        return response()->json($response);
    }

    public function regenerateTitle($id): JsonResponse
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
            $creativeTitle = $agent->creativeTitleAgent($articleContent);
            $inbound->post_title = $creativeTitle ? trim($creativeTitle, '"') : '';
            $inbound->save();

            return response()->json([
                'message' => 'Title regenerated successfully',
                'post_title' => $inbound->post_title
            ], 200);
        } catch (Exception $e) {
            Log::error('Regenerate title failed', ['error' => $e->getMessage()]);
            return response()->json(['message' => 'Error regenerating title'], 500);
        }
    }
}
