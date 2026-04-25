<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class AiCvScoringService
{
    protected string $apiKey;
    protected string $baseUrl;
    protected string $model;
    protected int $timeout;
    protected int $maxRetries = 3;

    public function __construct()
    {
        $provider = config('ai.provider', 'openai');

        $this->apiKey = config("ai.{$provider}.api_key", '');
        $this->baseUrl = config("ai.{$provider}.base_url");
        $this->model = config("ai.{$provider}.model");
        $this->timeout = config('ai.timeout', 60);
    }

    /**
     * تقييم سيرة ذاتية واحدة وإرجاع مصفوفة تحتوي على الدرجة والسبب
     */
    public function scoreCv(array $cvData): ?array
    {
        if (empty($this->apiKey)) {
            Log::error('AI Service: API Key is not configured. Please set AI_SERVICE_API_KEY in .env');
            return null;
        }

        $prompt = $this->buildPrompt($cvData);
        $provider = config('ai.provider', 'openai');

        $lastException = null;

        for ($attempt = 1; $attempt <= $this->maxRetries; $attempt++) {
            try {
                Log::info("AI Service: Attempt {$attempt}/{$this->maxRetries} for provider: {$provider}, model: {$this->model}");

                if ($provider === 'gemini') {
                    $result = $this->sendGeminiRequest($prompt, $cvData);
                } else {
                    $result = $this->sendOpenAiRequest($prompt);
                }

                if ($result !== null) {
                    Log::info("AI Service: Success on attempt {$attempt}", ['score' => $result['score']]);
                    return $result;
                }

                // If result is null but no exception, wait before retry
                if ($attempt < $this->maxRetries) {
                    $waitSeconds = $attempt * 3;
                    Log::info("AI Service: Null result, retrying in {$waitSeconds}s...");
                    sleep($waitSeconds);
                }
            } catch (\Exception $e) {
                $lastException = $e;
                Log::warning("AI Service: Attempt {$attempt} failed", [
                    'error' => $e->getMessage(),
                ]);

                if ($attempt < $this->maxRetries) {
                    $waitSeconds = $attempt * 3;
                    Log::info("AI Service: Retrying in {$waitSeconds}s...");
                    sleep($waitSeconds);
                }
            }
        }

        if ($lastException) {
            Log::error('AI Service: All retries failed', [
                'message' => $lastException->getMessage(),
            ]);
        }

        return null;
    }

    protected function sendGeminiRequest(string $prompt, array $cvData): ?array
    {
        $url = "{$this->baseUrl}/models/{$this->model}:generateContent?key={$this->apiKey}";

        $systemInstruction = 'You are a strict CV evaluator for the construction sector. You MUST respond with ONLY a valid JSON object (no markdown, no extra text). The JSON must have exactly two keys: "score" (integer 0-100) and "reason" (string with your evaluation in Arabic). Example: {"score": 75, "reason": "تقييم جيد"}';

        $parts = [
            ['text' => $prompt]
        ];

        // إرفاق ملف السيرة الذاتية إن وجد
        $cvFilePath = $cvData['cv_file_path'] ?? null;
        if ($cvFilePath && \Illuminate\Support\Facades\Storage::disk('public')->exists($cvFilePath)) {
            $absPath = \Illuminate\Support\Facades\Storage::disk('public')->path($cvFilePath);
            $mime = mime_content_type($absPath);
            $supportedMimes = ['application/pdf', 'image/jpeg', 'image/png', 'image/webp'];
            if (in_array($mime, $supportedMimes)) {
                $fileContent = base64_encode(file_get_contents($absPath));
                array_unshift($parts, [
                    'inlineData' => [
                        'mimeType' => $mime,
                        'data' => $fileContent
                    ]
                ]);
                $parts[] = ['text' => 'الملف المرفق أعلاه هو ملف السيرة الذاتية الأصلي. افحصه بعناية واستخدم محتواه في التقييم.'];
            }
        }

        $requestBody = [
            'system_instruction' => [
                'parts' => [
                    ['text' => $systemInstruction]
                ]
            ],
            'contents' => [
                [
                    'parts' => $parts
                ]
            ],
            'generationConfig' => [
                'temperature' => 0.2,
                'maxOutputTokens' => 1024,
                'responseMimeType' => 'application/json',
            ]
        ];

        $response = Http::withHeaders([
            'Content-Type' => 'application/json',
        ])
        ->withoutVerifying()
        ->timeout($this->timeout)
        ->post($url, $requestBody);

        if ($response->successful()) {
            $content = trim($response->json('candidates.0.content.parts.0.text', ''));
            Log::info('AI Service (Gemini): Raw response', ['content' => mb_substr($content, 0, 500)]);
            return $this->extractScoreAndReason($content);
        }

        $status = $response->status();
        $body = $response->body();

        Log::error("AI Service (Gemini): API request failed", [
            'status' => $status,
            'body' => mb_substr($body, 0, 500),
            'model' => $this->model,
            'url' => preg_replace('/key=.*/', 'key=***', $url),
        ]);

        // Throw exception for retryable errors (503, 429, 500)
        if (in_array($status, [503, 429, 500])) {
            throw new \RuntimeException("Gemini API returned {$status}: temporarily unavailable");
        }

        return null;
    }

    protected function sendOpenAiRequest(string $prompt): ?array
    {
        $response = Http::withHeaders([
            'Authorization' => "Bearer {$this->apiKey}",
            'Content-Type' => 'application/json',
        ])
        ->withoutVerifying()
        ->timeout($this->timeout)
        ->post("{$this->baseUrl}/chat/completions", [
            'model' => $this->model,
            'messages' => [
                [
                    'role' => 'system',
                    'content' => 'You are a strict CV evaluator. Respond with ONLY valid JSON: {"score": 75, "reason": "تعليق بالعربية"}. No markdown, no extra text.',
                ],
                [
                    'role' => 'user',
                    'content' => $prompt,
                ],
            ],
            'max_tokens' => 1000,
            'temperature' => 0.2,
        ]);

        if ($response->successful()) {
            $content = trim($response->json('choices.0.message.content', ''));
            Log::info('AI Service (OpenAI): Raw response', ['content' => mb_substr($content, 0, 500)]);
            return $this->extractScoreAndReason($content);
        }

        $status = $response->status();
        Log::error('AI Service (OpenAI): API request failed', [
            'status' => $status,
            'body' => mb_substr($response->body(), 0, 500),
        ]);

        if (in_array($status, [503, 429, 500])) {
            throw new \RuntimeException("OpenAI API returned {$status}: temporarily unavailable");
        }

        return null;
    }

    protected function extractScoreAndReason(string $content): ?array
    {
        // Step 1: Clean markdown wrappers
        $content = preg_replace('/```(?:json)?\s*/i', '', $content);
        $content = trim($content);

        // Step 2: Try direct JSON decode first
        $data = json_decode($content, true);
        if (is_array($data) && isset($data['score'])) {
            return $this->validateResult($data);
        }

        // Step 3: Fix control characters and retry JSON decode
        $cleaned = preg_replace('/[\x00-\x1f]/', ' ', $content);
        $data = json_decode($cleaned, true);
        if (is_array($data) && isset($data['score'])) {
            return $this->validateResult($data);
        }

        // Step 4: Extract JSON object from mixed content
        if (preg_match('/\{[^{}]*"score"\s*:\s*\d+[^{}]*\}/s', $cleaned, $matches)) {
            $data = json_decode($matches[0], true);
            if (is_array($data) && isset($data['score'])) {
                return $this->validateResult($data);
            }
        }

        // Step 5: Greedy approach - extract outermost braces
        if (preg_match('/\{.*\}/s', $cleaned, $matches)) {
            $data = json_decode($matches[0], true);
            if (is_array($data) && isset($data['score'])) {
                return $this->validateResult($data);
            }
        }

        // Step 6: Manual extraction - extract score and reason separately
        if (preg_match('/["\']?score["\']?\s*:\s*(\d+)/i', $cleaned, $scoreMatch)) {
            $score = (int) $scoreMatch[1];
            $reason = '';

            // Robust extraction: 'reason' is always the last field, so we match everything after it
            if (preg_match('/["\']?(?:reason|التعليق|السبب)["\']?\s*:\s*(.*)/is', $cleaned, $reasonMatch)) {
                $reason = $reasonMatch[1];
                $reason = preg_replace('/^["\']/', '', ltrim($reason));
                $reason = preg_replace('/["\']?\s*\}?\s*$/', '', rtrim($reason));
            }

            // Clean up the reason
            $reason = trim($reason);
            if (empty($reason)) {
                Log::warning('AI Service: Reason empty in Step 6, falling back to default', ['cleaned_content' => mb_substr($cleaned, 0, 500)]);
                $reason = 'لم يتم تقديم تعليق';
            }

            if ($score >= 0 && $score <= 100) {
                Log::info('AI Service: Returning result from Step 6', ['score' => $score, 'reason' => $reason]);
                return [
                    'score' => $score,
                    'reason' => $reason,
                ];
            }
        }

        // Step 7: Absolute last resort - extract any number as score
        if (preg_match('/\b(\d{1,3})\b/', $content, $numMatch)) {
            $score = (int) $numMatch[1];
            if ($score >= 0 && $score <= 100) {
                Log::warning("AI Service: Extracted bare score {$score} from content");
                return [
                    'score' => $score,
                    'reason' => 'لم يتم تقديم تعليق',
                ];
            }
        }

        Log::error('AI Service: Could not extract score from response', [
            'content' => mb_substr($content, 0, 1000),
        ]);
        return null;
    }

    protected function validateResult(array $data): ?array
    {
        $score = (int) ($data['score'] ?? -1);
        
        // Handle alternative keys for reason
        $reason = $data['reason'] ?? $data['التعليق'] ?? $data['السبب'] ?? null;
        
        if (empty($reason)) {
            Log::warning('AI Service: Reason empty in validateResult', ['data' => $data]);
            $reason = 'لم يتم تقديم تعليق';
        }

        if ($score >= 0 && $score <= 100) {
            Log::info('AI Service: Returning result from validateResult', ['score' => $score, 'reason' => $reason]);
            return [
                'score' => $score,
                'reason' => $reason,
            ];
        }

        Log::warning("AI Service: Score out of range: {$score}");
        return null;
    }

    /**
     * تقييم مجموعة من السير الذاتية
     */
    public function scoreBatch(array $cvsData): array
    {
        $results = [];
        foreach ($cvsData as $cvId => $cvData) {
            $results[$cvId] = $this->scoreCv($cvData);
        }
        return $results;
    }

    /**
     * بناء نص الطلب (Prompt) لتقييم السيرة الذاتية
     */
    protected function buildPrompt(array $cvData): string
    {
        $skills = $cvData['skills'] ?? 'غير محدد';
        $experience = $cvData['experience'] ?? 'غير محدد';
        $education = $cvData['education'] ?? 'غير محدد';
        $profileDetails = $cvData['profile_details'] ?? 'غير محدد';

        return <<<PROMPT
قيّم السيرة الذاتية التالية لمتقدم في مجال البناء والتشييد.

البيانات المدخلة:
- المهارات: {$skills}
- الخبرة: {$experience}
- المؤهلات العلمية: {$education}
- تفاصيل الملف الشخصي: {$profileDetails}

معايير التقييم:
1. المهارات وعمقها (40%): اخصم بشدة إذا كانت قليلة أو سطحية.
2. سنوات الخبرة وجودتها (40%): اخصم لمن تقل خبرته عن 10 سنوات.
3. المؤهلات العلمية والشهادات (20%): يتطلب درجات علمية متقدمة أو شهادات احترافية.

أرجع JSON فقط بهذه الصيغة:
{"score": 75, "reason": "التعليق هنا بالعربية في سطر واحد"}
PROMPT;
    }
}
