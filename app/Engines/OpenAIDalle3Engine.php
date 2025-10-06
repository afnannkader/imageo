<?php

namespace App\Engines;

use App\Traits\InteractWithImageGeneration;
use Exception;
use GuzzleHttp\Client;

class OpenAIDalle3Engine
{
    use InteractWithImageGeneration;

    public function process($engine, $prompt, $negative_prompt = null, $size, $samples, $storageProvider)
    {
        try {
            $generatedImages = $this->generate($engine, $prompt, $negative_prompt, $size, $samples);
            $result = [];
            foreach ($generatedImages as $key => $image) {
                $image = $this->downloadImage($image);
                $result[$key] = $this->imageProcess($image, $storageProvider);
            }
            return $result;
        } catch (Exception $e) {
            return $e->getMessage();
        }
    }

    private function generate($engine, $prompt, $negative_prompt = null, $size, $samples)
    {
        try {
            $body = [
                'model' => 'dall-e-3',
                'prompt' => $prompt,
                'quality' => 'hd',
                'n' => (int) $samples,
                'size' => $size,
            ];

            $client = new Client([
                'base_uri' => 'https://api.openai.com/v1/',
                'headers' => [
                    'Authorization' => 'Bearer ' . $engine->credentials->api_key,
                    'Content-Type' => 'application/json',
                ],
            ]);

            $response = $client->post('images/generations', [
                'json' => $body,
            ]);

            $response = json_decode($response->getBody(), true);

            $imageUrls = [];
            foreach ($response['data'] as $image) {
                $imageUrls[] = $image['url'];
            }

            return $imageUrls;
        } catch (Exception $e) {
            throw new Exception($e->getMessage());
        }
    }
}
