<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class AIService
{
    private $apiKey;
    private $baseUrl = 'https://api.openai.com/v1';

    public function __construct()
    {
        $this->apiKey = config('services.openai.api_key');
    }

    /**
     * Answer a student's question based on lesson content
     */
    public function answerQuestion($lesson, $question)
    {
        try {
            $prompt = $this->buildPrompt($lesson, $question);
            
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->apiKey,
                'Content-Type' => 'application/json',
            ])->post($this->baseUrl . '/chat/completions', [
                'model' => 'gpt-3.5-turbo',
                'messages' => [
                    [
                        'role' => 'system',
                        'content' => 'You are a helpful educational assistant. Answer questions based only on the provided lesson content. Be clear, educational, and encouraging.'
                    ],
                    [
                        'role' => 'user',
                        'content' => $prompt
                    ]
                ],
                'max_tokens' => 500,
                'temperature' => 0.7,
            ]);

            if ($response->successful()) {
                return $response->json('choices.0.message.content');
            }

            Log::error('OpenAI API Error', ['response' => $response->body()]);
            return 'I apologize, but I cannot process your question right now. Please try again later.';

        } catch (\Exception $e) {
            Log::error('AI Service Error', ['error' => $e->getMessage()]);
            return 'An error occurred while processing your question. Please try again.';
        }
    }

    /**
     * Get lesson recommendations based on current lesson and question
     */
    public function recommendLessons($currentLesson, $question, $allLessons)
    {
        try {
            $prompt = "Based on this lesson: '{$currentLesson->title}' and student question: '{$question}', 
                      which of these lessons would be most helpful for further learning? 
                      Return only lesson IDs as a comma-separated list.
                      
                      Available lessons:";
            
            foreach ($allLessons as $lesson) {
                if ($lesson->id !== $currentLesson->id) {
                    $prompt .= "\nID: {$lesson->id}, Title: {$lesson->title}";
                }
            }

            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->apiKey,
                'Content-Type' => 'application/json',
            ])->post($this->baseUrl . '/chat/completions', [
                'model' => 'gpt-3.5-turbo',
                'messages' => [
                    [
                        'role' => 'system',
                        'content' => 'You are an educational content recommender. Return only lesson IDs as comma-separated numbers.'
                    ],
                    [
                        'role' => 'user',
                        'content' => $prompt
                    ]
                ],
                'max_tokens' => 100,
                'temperature' => 0.3,
            ]);

            if ($response->successful()) {
                $recommendedIds = $response->json('choices.0.message.content');
                return $this->parseRecommendedIds($recommendedIds, $allLessons);
            }

            return collect();

        } catch (\Exception $e) {
            Log::error('Recommendation Error', ['error' => $e->getMessage()]);
            return collect();
        }
    }

    /**
     * Build the prompt for AI question answering
     */
    private function buildPrompt($lesson, $question)
    {
        return "Lesson Title: {$lesson->title}\n\n" .
               "Lesson Content:\n{$lesson->content}\n\n" .
               "Student Question: {$question}\n\n" .
               "Please provide a helpful answer based on the lesson content above. " .
               "If the question cannot be answered from the lesson content, " .
               "kindly let the student know and suggest they ask their teacher for more information.";
    }

    /**
     * Parse recommended lesson IDs from AI response
     */
    private function parseRecommendedIds($aiResponse, $allLessons)
    {
        $ids = collect(explode(',', $aiResponse))
            ->map(fn($id) => trim($id))
            ->filter(fn($id) => is_numeric($id))
            ->map(fn($id) => (int) $id);

        return $allLessons->whereIn('id', $ids)->take(3);
    }

    /**
     * Generate a summary of the lesson for quick understanding
     */
    public function summarizeLesson($lesson)
    {
        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->apiKey,
                'Content-Type' => 'application/json',
            ])->post($this->baseUrl . '/chat/completions', [
                'model' => 'gpt-3.5-turbo',
                'messages' => [
                    [
                        'role' => 'system',
                        'content' => 'Summarize the following lesson in 2-3 sentences, highlighting the key learning points.'
                    ],
                    [
                        'role' => 'user',
                        'content' => $lesson->content
                    ]
                ],
                'max_tokens' => 150,
                'temperature' => 0.5,
            ]);

            if ($response->successful()) {
                return $response->json('choices.0.message.content');
            }

            return 'Summary not available.';

        } catch (\Exception $e) {
            Log::error('Summarization Error', ['error' => $e->getMessage()]);
            return 'Summary not available.';
        }
    }
}