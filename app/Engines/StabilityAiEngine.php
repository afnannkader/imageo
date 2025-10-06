<?php

namespace App\Engines;

use App\Traits\InteractWithImageGeneration;
use Exception;
use GuzzleHttp\Client;

class StabilityAiEngine
{
    use InteractWithImageGeneration;

    public function process($engine, $prompt, $negative_prompt = null, $size, $samples, $storageProvider)
    {
        try {
            $generatedImages = $this->generate($engine, $prompt, $negative_prompt, $size, $samples);
            $result = [];
            foreach ($generatedImages as $key => $image) {
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
            $apiKey = $engine->credentials->api_key;
            $size = explode('x', $size);
            $client = new Client();

            $textPrompts = [[
                'text' => $prompt,
                'weight' => 1,
            ]];

            if ($negative_prompt) {
                $textPrompts[] = [
                    'text' => $negative_prompt,
                    'weight' => -1,
                ];
            }

            $response = $client->post("https://api.stability.ai/v1/generation/{$engine->alias}/text-to-image", [
                'headers' => [
                    'Accept' => 'application/json',
                    'Authorization' => 'Bearer ' . $apiKey,
                ],
                'json' => [
                    'steps' => 50,
                    'width' => (int) $size[0],
                    'height' => (int) $size[1],
                    'seed' => 0,
                    'cfg_scale' => 7,
                    'samples' => (int) $samples,
                    'style_preset' => $style ?? null,
                    'text_prompts' => $textPrompts,
                ],
            ]);

            $responseJSON = json_decode($response->getBody(), true);
            return array_map(fn($image) => base64_decode($image['base64']), $responseJSON['artifacts']);
        } catch (Exception $e) {
            throw new Exception($e->getMessage());
        }
    }
}
