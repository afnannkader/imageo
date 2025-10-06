<?php

namespace App\Engines;

use App\Traits\InteractWithImageGeneration;
use Exception;
use GuzzleHttp\Client;

class StableImageCoreEngine
{
    use InteractWithImageGeneration;

    public function process($engine, $prompt, $negative_prompt = null, $size, $samples, $storageProvider)
    {
        try {
            $generatedImage = $this->generate($engine, $prompt, $negative_prompt, $size, $samples);
            $result[0] = $this->imageProcess($generatedImage, $storageProvider);
            return $result;
        } catch (Exception $e) {
            return $e->getMessage();
        }
    }

    private function generate($engine, $prompt, $negative_prompt = null, $size, $samples)
    {
        try {
            $apiKey = $engine->credentials->api_key;

            $client = new Client();

            $multipartData = [
                [
                    'name' => 'prompt',
                    'contents' => $prompt,
                ],
                [
                    'name' => 'negative_prompt',
                    'contents' => $negative_prompt ?? '',
                ],
                [
                    'name' => 'aspect_ratio',
                    'contents' => $size,
                ],
                [
                    'name' => 'seed',
                    'contents' => 0,
                ],
                [
                    'name' => 'output_format',
                    'contents' => 'jpeg',
                ],
                [
                    'name' => 'width',
                    'contents' => (int) $size[0],
                ],
                [
                    'name' => 'height',
                    'contents' => (int) $size[1],
                ],
            ];

            $response = $client->post("https://api.stability.ai/v2beta/stable-image/generate/core", [
                'headers' => [
                    'Authorization' => 'Bearer ' . $apiKey,
                    'Accept' => 'application/json',
                ],
                'multipart' => $multipartData,
            ]);

            $response = json_decode($response->getBody(), true);

            return base64_decode($response['image']);
        } catch (Exception $e) {
            throw new Exception($e->getMessage());
        }
    }
}
