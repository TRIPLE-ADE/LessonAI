<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class AIService
{
    protected string $baseUrl;
    protected string $apiKey;

    public function __construct()
    {
        $this->baseUrl = 'https://generativelanguage.googleapis.com/v1beta/models/gemini-2.0-flash:generateContent';
        $this->apiKey = env('GEMINI_API_KEY');
    }

    /**
     * Answer a student's question based on lesson content
     */
    public function answerQuestion($lesson, $question)
    {
        try {
            $prompt = $this->buildPrompt($lesson, $question);
            Log::info('AI Prompt', ['prompt' => $prompt]);

             $response = Http::withOptions([
                'verify' => false, 
                'timeout' => 30,
    
            ])->withHeaders([
                'Content-Type' => 'application/json',
            ])->post($this->baseUrl . '?key=' . $this->apiKey, [
                'contents' => [
                    [
                        'parts' => [
                            ['text' => $prompt]
                        ]
                    ]
                ]
            ]);

            if ($response->successful()) {
                Log::info('AI Response', ['response' => $response->body()]);
                // Gemini returns candidates[0].content.parts[0].text
                return $response->json('candidates.0.content.parts.0.text');
            }

            Log::error('Gemini API Error', ['response' => $response->body()]);
            return 'I apologize, but I cannot process your question right now. Please try again later.';

        } catch (\Exception $e) {
            Log::error('AI Service Error', ['error' => $e->getMessage()]);
            return 'An error occurred while processing your question. Please try again.' . $e->getMessage();
        }
    }

    /**
     * Get lesson recommendations based on current lesson and question
     */
    public function recommendLessons($currentLesson, $question, $allLessons)
    {
        try {
            $prompt = "Based on this lesson: '{$currentLesson->title}' and student question: '{$question}', which of these lessons would be most helpful for further learning? Return only lesson IDs as a comma-separated list.\n\nAvailable lessons:";
            foreach ($allLessons as $lesson) {
                if ($lesson->id !== $currentLesson->id) {
                    $prompt .= "\nID: {$lesson->id}, Title: {$lesson->title}";
                }
            }

            $response = Http::withHeaders([
                'Content-Type' => 'application/json',
            ])->post($this->baseUrl . '?key=' . $this->apiKey, [
                'contents' => [
                    [
                        'parts' => [
                            ['text' => $prompt]
                        ]
                    ]
                ]
            ]);

            if ($response->successful()) {
                $recommendedIds = $response->json('candidates.0.content.parts.0.text');
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
            $prompt = "Summarize the following lesson in 2-3 sentences, highlighting the key learning points:\n\n" . $lesson->content;

            $response = Http::withHeaders([
                'Content-Type' => 'application/json',
            ])->post($this->baseUrl . '?key=' . $this->apiKey, [
                'contents' => [
                    [
                        'parts' => [
                            ['text' => $prompt]
                        ]
                    ]
                ]
            ]);

            if ($response->successful()) {
                return $response->json('candidates.0.content.parts.0.text');
            }

            return 'Summary not available.';

        } catch (\Exception $e) {
            Log::error('Summarization Error', ['error' => $e->getMessage()]);
            return 'Summary not available.';
        }
    }
}