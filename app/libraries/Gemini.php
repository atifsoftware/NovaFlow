<?php

namespace NovaFlow\Core;

/**
 * Gemini AI Helper Library
 * Handles communication with Google Gemini Pro API
 */
class Gemini
{
    private $apiKey;
    private $apiUrl = 'https://generativelanguage.googleapis.com/v1/models/gemini-2.0-flash:generateContent';

    public function __construct($apiKey)
    {
        $this->apiKey = $apiKey;
    }

    /**
     * Send a prompt to Gemini and get response
     * 
     * @param string $prompt
     * @param string $systemInstruction Optional system instruction
     * @return string|bool
     */
    public function generateResponse($prompt, $systemInstruction = '')
    {
        if (empty($this->apiKey)) {
            return "Error: API Key is missing. Please set it in Admin Settings.";
        }

        $url = $this->apiUrl . '?key=' . $this->apiKey;

        // Use a better personality if system instruction is provided
        if (!empty($systemInstruction)) {
            $data = [
                'system_instruction' => [
                    'parts' => [
                        ['text' => $systemInstruction]
                    ]
                ],
                'contents' => [
                    [
                        'role' => 'user',
                        'parts' => [
                            ['text' => $prompt]
                        ]
                    ]
                ],
                'generationConfig' => [
                    'temperature' => 0.7,
                    'topK' => 40,
                    'topP' => 0.95,
                    'maxOutputTokens' => 1024,
                ]
            ];
        } else {
            $data = [
                'contents' => [
                    [
                        'role' => 'user',
                        'parts' => [
                            ['text' => $prompt]
                        ]
                    ]
                ],
                'generationConfig' => [
                    'temperature' => 0.7,
                    'topK' => 40,
                    'topP' => 0.95,
                    'maxOutputTokens' => 1024,
                ]
            ];
        }

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json'
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        if (curl_errno($ch)) {
            $error = curl_error($ch);
            curl_close($ch);
            return "Connection Error: " . $error;
        }

        curl_close($ch);

        if ($httpCode !== 200) {
            $errData = json_decode($response, true);
            $errMsg = $errData['error']['message'] ?? 'Unknown API Error';
            return "API Error ($httpCode): " . $errMsg;
        }

        $result = json_decode($response, true);

        if (isset($result['candidates'][0]['content']['parts'][0]['text'])) {
            return $result['candidates'][0]['content']['parts'][0]['text'];
        }

        return "Error: Could not parse AI response.";
    }
}
