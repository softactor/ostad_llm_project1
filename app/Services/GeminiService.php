<?php

namespace App\Services;

use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class GeminiService
{
    /**
     * The API key for Gemini services (if direct connection is used later).
     */
    private string $apiKey;

    /**
     * The designated model identifier string (e.g., 'gemini-1.5-flash').
     */
    private string $model;

    /**
     * Base endpoint URL for OpenRouter's chat completion API wrapper.
     */
    private string $baseUrl = 'https://openrouter.ai/api/v1/chat/completions';

    /**
     * GeminiService constructor.
     * Pulls necessary API configurations and fallbacks from Laravel service config files.
     */
    public function __construct()
    {
        $this->apiKey = config('services.openrouter.key');
        $this->model  = config('services.gemini.model', 'gemini-1.5-flash');
    }

    /**
     * Send a message payload containing full conversational history thread context.
     *
     * @param  array   $history       Array of steps: [['role' => 'user'|'model', 'parts' => [['text' => '...']]]]
     * @param  string  $systemPrompt  Optional behavioral rules or persona context for the model
     * @return string                 The model's text response or an explicit error message
     */
    public function chat(array $history, string $systemPrompt = ''): string
    {
        $messages = [];

        // 1. Inject the system persona prompt at the beginning of the payload if present
        if (!empty($systemPrompt)) {
            $messages[] = ['role' => 'system', 'content' => $systemPrompt];
        }

        // 2. Transpile Gemini array configuration into OpenAI-compatible format
        foreach ($history as $turn) {
            $messages[] = [
                // Map Gemini's 'model' moniker back to OpenAI's 'assistant' moniker
                'role'    => $turn['role'] === 'model' ? 'assistant' : 'user',
                'content' => $turn['parts'][0]['text'],
            ];
        }

        try {
            // 3. Dispatch the HTTP POST payload to OpenRouter
            $response = Http::timeout(30)
                ->withHeaders([
                    'Authorization' => 'Bearer ' . config('services.openrouter.key'),
                    'HTTP-Referer'  => config('app.url'), // OpenRouter requirement to rank app context
                ])
                ->post($this->baseUrl, [
                    'model'    => $this->model,
                    'messages' => $messages,
                ])
                ->throw();

            // 4. Extract data using dot notation fallback safely if string response evaluates to null
            return $response->json('choices.0.message.content')
                ?? 'Error: Empty response content received from the model.';

        } catch (RequestException $e) {
            // Catch and log explicit API network payload and validation error returns
            Log::error('OpenRouter API Request Exception', [
                'status' => $e->response->status(),
                'body'   => $e->response->body()
            ]);

            return 'API Error: ' . $e->response->json('error.message', 'An unknown API error occurred.');

        } catch (\Throwable $e) {
            // Catch and log unexpected structural breakdowns or connection terminations
            Log::error('GeminiService Critical Exception', [
                'message' => $e->getMessage(),
                'trace'   => $e->getTraceAsString()
            ]);

            return 'Internal Server Error: Unable to process your request at this time.';
        }
    }

    /**
     * Standardizes a user chat node into a valid Gemini 'contents' array segment.
     *
     * @param  string  $text  The user message string
     * @return array          Gemini formatted user chat segment
     */
    public static function userTurn(string $text): array
    {
        return ['role' => 'user', 'parts' => [['text' => $text]]];
    }

    /**
     * Standardizes an assistant/model chat node into a valid Gemini 'contents' array segment.
     *
     * @param  string  $text  The model output string
     * @return array          Gemini formatted model chat segment
     */
    public static function modelTurn(string $text): array
    {
        return ['role' => 'model', 'parts' => [['text' => $text]]];
    }
}
