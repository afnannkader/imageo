<?php

namespace App\Engines;

use App\Traits\InteractWithImageGeneration;
use Exception;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;

class ReplicateNSFWFluxEngine
{
    use InteractWithImageGeneration;

    public function process($engine, $prompt, $size, $samples, $storageProvider)
    {
        try {

            set_time_limit(120);

            $generatedImages = $this->generate($engine, $prompt, $size, $samples);
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

    private function generate($engine, $prompt, $size, $samples)
    {
        try {
            $apiToken = $engine->credentials->api_token;
            $client = new Client();
    
            $response = $client->request('POST', "https://api.replicate.com/v1/predictions", [
                'headers' => [
                    'Authorization' => 'Token ' . $apiToken,
                    'Content-Type' => 'application/json',
                    
                ],
                'json' => [
                    'version' => 'fb4f086702d6a301ca32c170d926239324a7b7b2f0afc3d232a9c4be382dc3fa',
                    'input' => [
                        'prompt' => $prompt,
                        'guidence' => 3.5,
                        'aspect_ratio' => $size,
                        'output_format' => 'jpg',
                        'output_quality' => 100,
                        'num_outputs' => (int) $samples,
                    ],
                ],
                 'timeout' => 120,
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
