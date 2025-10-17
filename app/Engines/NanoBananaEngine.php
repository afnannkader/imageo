<?php

namespace App\Engines;

use App\Traits\InteractWithImageGeneration;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Log;

class NanoBananaEngine
{
    use InteractWithImageGeneration;

    /**
     * Submit to Replicate and return raw JSON.
     */
    public function generate(array $imageUrls, string $prompt, string $outputFormat = 'jpg', ?string $apiToken = null): array
    {
        $client = new Client();
        $response = $client->post('https://api.replicate.com/v1/models/google/nano-banana/predictions', [
            'headers' => [
                'Authorization' => 'Bearer ' . ($apiToken ?? env('REPLICATE_API_TOKEN')),
                'Content-Type' => 'application/json',
                'Prefer' => 'wait',
            ],
            'json' => [
                'input' => [
                    'prompt' => $prompt,
                    'image_input' => array_values($imageUrls),
                    'output_format' => $outputFormat,
                ],
            ],
            'timeout' => 180,
        ]);

        $body = (string) $response->getBody();
        $decoded = json_decode($body, true) ?: [];
        Log::info(['nano_banana_api_response' => $decoded]);
        return $decoded;
    }

    /**
     * Convenience method: generate, then download and upload via storage provider.
     * Returns array of processed images: [ ['main' => ..., 'thumbnail' => ...], ... ]
     */
    public function process(array $imageUrls, string $prompt, $storageProvider, string $outputFormat = 'jpg'): array
    {
        $data = $this->generate($imageUrls, $prompt, $outputFormat);
        $outputs = [];
        if (isset($data['output'])) {
            if (is_array($data['output'])) {
                foreach ($data['output'] as $idx => $url) {
                    $imageStream = $this->downloadImage($url);
                    $outputs[$idx] = $this->imageProcess($imageStream, $storageProvider);
                }
            } elseif (is_string($data['output'])) {
                $imageStream = $this->downloadImage($data['output']);
                $outputs[0] = $this->imageProcess($imageStream, $storageProvider);
            }
        }
        return $outputs;
    }
}


