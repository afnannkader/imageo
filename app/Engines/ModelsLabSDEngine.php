<?php

namespace App\Engines;

use App\Traits\InteractWithImageGeneration;
use Exception;
use GuzzleHttp\Client;

class ModelsLabSDEngine
{
    use InteractWithImageGeneration;

    private $client;

    public function __construct()
    {
        $this->client = new Client([
            'base_uri' => 'https://modelslab.com/api/v6/realtime/',
            'headers' => [
                'Content-Type' => 'application/json',
            ],
        ]);
    }

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
            $apiKey = $engine->credentials->api_key;
            $size = explode('x', $size);

            $body = [
                'key' => $apiKey,
                'prompt' => $prompt,
                'width' => (int) $size[0],
                'height' => (int) $size[1],
                'samples' => (int) $samples,
                'num_inference_steps' => '30',
                'guidance_scale' => 7.5,
            ];

            if ($negative_prompt) {
                $body['negative_prompt'] = $negative_prompt;
            }

            $response = $this->client->post('text2img', [
                'json' => $body,
            ]);
            $response = json_decode($response->getBody(), true);

            $imageUrls = null;
            if ($response['status'] == "success") {
                $imageUrls = $response['output'];
            } else {
                $retries = 0;
                while ($response['status'] == 'processing' && $retries < 10) {
                    sleep(5);
                    $fetchData = $this->client->post('fetch/' . $response['id'], [
                        'json' => [
                            'key' => $apiKey,
                        ],
                    ]);
                    $output = json_decode($fetchData->getBody(), true);
                    if ($output['status'] == 'success') {
                        $imageUrls = $output['output'];
                    }
                    $retries++;
                }
            }

            return $imageUrls;
        } catch (Exception $e) {
            throw new Exception($e->getMessage());
        }
    }
}
