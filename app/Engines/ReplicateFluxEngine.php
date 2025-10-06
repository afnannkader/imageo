<?php

namespace App\Engines;

use App\Traits\InteractWithImageGeneration;
use Exception;
use GuzzleHttp\Client;

class ReplicateFluxEngine
{
    use InteractWithImageGeneration;

    public function process($engine, $prompt, $negative_prompt = null, $size, $samples, $storageProvider)
    {
        try {
            $generatedImages = $this->generate($engine, $prompt, $negative_prompt, $size, $samples);
            $result = [];
            if (is_array($generatedImages)) {
                foreach ($generatedImages as $key => $image) {
                    $image = $this->downloadImage($image);
                    $result[$key] = $this->imageProcess($image, $storageProvider);
                }
            } else {
                $image = $this->downloadImage($generatedImages);
                $result[0] = $this->imageProcess($image, $storageProvider);
            }
            return $result;
        } catch (Exception $e) {
            return $e->getMessage();
        }
    }

    private function generate($engine, $prompt, $negative_prompt = null, $size, $samples)
    {
        try {
            $apiToken = $engine->credentials->api_token;
            $endpoint = "https://api.replicate.com/v1/models/black-forest-labs/{$engine->alias}/predictions";

            $client = new Client();

            $response = $client->request('POST', $endpoint, [
                'headers' => [
                    'Authorization' => 'Bearer ' . $apiToken,
                    'Content-Type' => 'application/json',
                ],
                'json' => [
                    'input' => [
                        'prompt' => $prompt,
                        'guidance' => 3.5,
                        'aspect_ratio' => $size,
                        'output_format' => 'jpg',
                        'output_quality' => 100,
                        'num_outputs' => (int) $samples,
                    ],
                ],
            ]);

            $responseData = json_decode($response->getBody(), true);

            $predictionId = $responseData['id'];

            $status = $responseData['status'];
            while (in_array($status, ['starting', 'processing'])) {
                sleep(5);
                $statusResponse = $this->checkPredictionStatus($client, $apiToken, $predictionId);
                $status = $statusResponse['status'];
            }

            if ($statusResponse['status'] === 'succeeded') {
                return $statusResponse['output'];
            }

            throw new Exception($statusResponse['error'] ?? 'Unknown error occurred.');
        } catch (RequestException $e) {
            throw new Exception($e->getMessage());
        }
    }

    private function checkPredictionStatus(Client $client, $apiToken, $predictionId)
    {
        try {
            $response = $client->request('GET', "https://api.replicate.com/v1/predictions/{$predictionId}", [
                'headers' => [
                    'Authorization' => 'Bearer ' . $apiToken,
                ],
            ]);

            return json_decode($response->getBody(), true);
        } catch (RequestException $e) {
            throw new Exception($e->getMessage());
        }
    }
}
