<?php

namespace App\Services;

use OpenAI;
use Exception;
use Illuminate\Support\Facades\Log;

class OpenAIService
{
    private $client;

    public function __construct()
    {
        $this->client = OpenAI::client(config('services.openai.api_key'));
    }

    /**
     * Parse a natural language query to extract part information
     */
    public function parsePartQuery(string $query): array
    {
        try {
            $response = $this->client->chat()->create([
                'model' => config('services.openai.model', 'gpt-4-turbo-preview'),
                'messages' => [
                    [
                        'role' => 'system',
                        'content' => 'You are a helpful assistant for an aircraft parts search system. Your task is to extract part numbers or part identifiers from natural language queries. 
                        
                        Users might ask questions like:
                        - "Find part number ABC123"
                        - "I need pricing for ABC123"
                        - "Search for ABC123 from Boeing"
                        - "Do you have ABC123 in stock?"
                        - "What\'s the price of part ABC123?"
                        
                        Extract the part number and respond in JSON format like:
                        {
                            "success": true,
                            "part_number": "ABC123",
                            "additional_context": "any additional context from the query"
                        }
                        
                        If you cannot identify a part number, respond with:
                        {
                            "success": false,
                            "message": "Please provide a specific part number to search for."
                        }'
                    ],
                    [
                        'role' => 'user',
                        'content' => $query
                    ]
                ],
                'temperature' => 0.3,
                'max_tokens' => 150,
                'response_format' => ['type' => 'json_object']
            ]);

            $content = $response->choices[0]->message->content;
            return json_decode($content, true);
        } catch (Exception $e) {
            Log::error('OpenAI parsing error: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Sorry, I couldn\'t understand your request. Please try again.'
            ];
        }
    }

    /**
     * Generate a conversational response for the chat
     */
    public function generateChatResponse(string $userMessage, string $context = ''): string
    {
        try {
            $systemMessage = 'You are a helpful assistant for an aircraft parts search system. You help users search for aircraft parts and understand their queries. Be concise and professional.';
            
            if ($context) {
                $systemMessage .= "\n\nContext: " . $context;
            }

            $response = $this->client->chat()->create([
                'model' => config('services.openai.model', 'gpt-4-turbo-preview'),
                'messages' => [
                    [
                        'role' => 'system',
                        'content' => $systemMessage
                    ],
                    [
                        'role' => 'user',
                        'content' => $userMessage
                    ]
                ],
                'temperature' => 0.7,
                'max_tokens' => 200
            ]);

            return $response->choices[0]->message->content;
        } catch (Exception $e) {
            Log::error('OpenAI chat response error: ' . $e->getMessage());
            return 'I apologize, but I encountered an error processing your request. Please try again.';
        }
    }
}
