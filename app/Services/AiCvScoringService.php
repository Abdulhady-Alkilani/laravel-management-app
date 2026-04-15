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

    public function __construct()
    {
        $provider = config('ai.provider', 'openai');

        $this->apiKey = config("ai.{$provider}.api_key", '');
        $this->baseUrl = config("ai.{$provider}.base_url");
        $this->model = config("ai.{$provider}.model");
        $this->timeout = config('ai.timeout', 30);
    }

    /**
     * تقييم سيرة ذاتية واحدة وإرجاع الدرجة (0-100)
     */
    public function scoreCv(array $cvData): ?int
    {
        if (empty($this->apiKey)) {
            Log::error('AI Service: API Key is not configured. Please set AI_SERVICE_API_KEY in .env');
            return null;
        }

        $prompt = $this->buildPrompt($cvData);
        $provider = config('ai.provider', 'openai');

        try {
            if ($provider === 'gemini') {
                return $this->sendGeminiRequest($prompt, $cvData);
            } else {
                return $this->sendOpenAiRequest($prompt);
            }
        } catch (\Exception $e) {
            Log::error('AI Service: Exception occurred', [
                'message' => $e->getMessage(),
            ]);
            return null;
        }
    }

    protected function sendGeminiRequest(string $prompt, array $cvData): ?int
    {
        // Google AI Studio (Gemini) Endpoint format:
        // https://generativelanguage.googleapis.com/v1beta/models/gemini-1.5-flash:generateContent?key=API_KEY
        $url = "{$this->baseUrl}/models/{$this->model}:generateContent?key={$this->apiKey}";

        $parts = [
            ['text' => 'أنت خبير توظيف ومُقيّم صارم جداً للسير الذاتية في قطاع البناء والتشييد. أرجع التقييم كرقم صحيح فقط من 0 إلى 100.' . "\n\n" . $prompt]
        ];

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
                $parts[] = ['text' => 'تم إرفاق الملف الأصلي للـ CV أيضاً، يرجى فحصه بعناية وتضمين محتواه في تقييمك للسيرة الذاتية بجانب البيانات المدخلة.'];
            }
        }

        $response = Http::withHeaders([
            'Content-Type' => 'application/json',
        ])
        ->withoutVerifying()
        ->timeout($this->timeout)
        ->post($url, [
            'contents' => [
                [
                    'parts' => $parts
                ]
            ],
            'generationConfig' => [
                'temperature' => 0.1,
                'maxOutputTokens' => 100,
            ]
        ]);

        if ($response->successful()) {
            $content = trim($response->json('candidates.0.content.parts.0.text', ''));
            return $this->extractScore($content);
        }

        Log::error('AI Service (Gemini): API request failed', [
            'status' => $response->status(),
            'body' => $response->body(),
        ]);
        return null;
    }

    protected function sendOpenAiRequest(string $prompt): ?int
    {
        $response = Http::withHeaders([
            'Authorization' => "Bearer {$this->apiKey}",
            'Content-Type' => 'application/json',
        ])
        ->timeout($this->timeout)
        ->post("{$this->baseUrl}/chat/completions", [
            'model' => $this->model,
            'messages' => [
                [
                    'role' => 'system',
                    'content' => 'أنت خبير توظيف ومُقيّم صارم جداً للسير الذاتية في قطاع البناء والتشييد. قيّم السيرة الذاتية المقدمة بحزم وبناءً على شروط قاسية للخبرة الطويلة وتعدد المهارات. أرجع الدرجة فقط كرقم صحيح من 0 إلى 100 بدون أي نص إضافي.',
                ],
                [
                    'role' => 'user',
                    'content' => $prompt,
                ],
            ],
            'max_tokens' => 10,
            'temperature' => 0.1,
        ]);

        if ($response->successful()) {
            $content = trim($response->json('choices.0.message.content', ''));
            return $this->extractScore($content);
        }

        Log::error('AI Service (OpenAI/DeepSeek): API request failed', [
            'status' => $response->status(),
            'body' => $response->body(),
        ]);
        return null;
    }
    protected function extractScore(string $content): ?int
    {
        preg_match('/\d+/', $content, $matches);
        $score = isset($matches[0]) ? (int) $matches[0] : null;

        if ($score !== null && $score >= 0 && $score <= 100) {
            return $score;
        }

        Log::warning("AI Service: Invalid score returned: {$content}");
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
قم بثقييم السيرة الذاتية التالية لمتقدم في مجال البناء والتشييد بصرامة شديدة. التقييم العالي يتطلب عدداً كبيراً من المهارات الاحترافية القوية وخبرة عملية واسعة وطويلة. المبتدئون أو من لديهم مهارات سطحية يجب أن يحصلوا على درجات منخفضة.

**المهارات:** {$skills}
**سنوات الخبرة:** {$experience}
**المؤهلات العلمية:** {$education}
**تفاصيل الملف الشخصي:** {$profileDetails}

المعايير الصارمة:
- كثرة المهارات وعمقها واحترافيتها (40%): اخصم النقاط بشدة إذا كانت المهارات قليلة أو غير متقدمة.
- ثقل سنوات الخبرة وجودتها (40%): اخصم النقاط لمن تقل خبرته عن 10 سنوات، وقيّم المناصب والإنجازات بصرامة.
- المؤهلات العلمية والشهادات (20%): التقييم الممتاز يتطلب درجات علمية متقدمة أو شهادات احترافية قوية.

بناءً على التشدد التام في التقييم، أرجع الدرجة النهائية فقط كرقم صحيح من 0 إلى 100.
PROMPT;
    }
}
