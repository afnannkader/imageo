<?php

namespace App\Engines;

use Exception;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use Illuminate\Support\Facades\Log;

class NanoBananaEngine
{
    public function process($engine, $imagePath, $prompt = null, $negative_prompt = null, $size = '1024x1024', $samples = 1, $storageProvider = null)
    {
        try {
            Log::info('NanoBananaEngine process called', [
                'engine_id' => $engine->id,
                'image_path' => $imagePath,
                'prompt' => $prompt,
            ]);

            set_time_limit(120);

            $generatedImages = $this->generate($engine, $imagePath, $prompt, $negative_prompt, $size, $samples);
            $result = [];

            if (!is_array($generatedImages)) {
                $generatedImages = [$generatedImages];
            }

            // Create folder if not exists
            $folder = public_path('generated-image/');
            if (!file_exists($folder)) {
                mkdir($folder, 0777, true);
            }

            foreach ($generatedImages as $key => $imageUrl) {
                $client = new \GuzzleHttp\Client();
                $response = $client->get($imageUrl);
                $imageContent = $response->getBody()->getContents();

                // Save main image
                $filename = uniqid('NB_') . '_' . time() . '.jpg';
                $fullPath = $folder . $filename;
                file_put_contents($fullPath, $imageContent);

                // Create thumbnail without Intervention Image
                $thumbnailFilename = 'thumb_' . $filename;
                $thumbnailFullPath = $folder . $thumbnailFilename;

                $this->createThumbnail($fullPath, $thumbnailFullPath, 300, 300);

                $result[$key] = [
                    'main' => [
                        'filename' => $filename,
                        'path' => 'generated-image/' . $filename,
                        'url' => url('generated-image/' . $filename),
                    ],
                    'thumbnail' => [
                        'filename' => $thumbnailFilename,
                        'path' => 'generated-image/' . $thumbnailFilename,
                        'url' => url('generated-image/' . $thumbnailFilename),
                    ]
                ];

                Log::info('✅ Generated image and thumbnail saved', ['main' => $fullPath, 'thumbnail' => $thumbnailFullPath]);
            }

            return $result;

        } catch (\Exception $e) {
            Log::error('NanoBananaEngine Error', ['message' => $e->getMessage()]);
            return $e->getMessage();
        }
    }

    /**
     * Create a thumbnail using native PHP GD functions
     */
    private function createThumbnail($sourcePath, $destPath, $thumbWidth, $thumbHeight)
    {
        $info = getimagesize($sourcePath);
        $width = $info[0];
        $height = $info[1];
        $mime = $info['mime'];

        // Load the image based on mime type
        switch ($mime) {
            case 'image/jpeg':
                $sourceImage = imagecreatefromjpeg($sourcePath);
                break;
            case 'image/png':
                $sourceImage = imagecreatefrompng($sourcePath);
                break;
            case 'image/gif':
                $sourceImage = imagecreatefromgif($sourcePath);
                break;
            default:
                throw new \Exception('Unsupported image type: ' . $mime);
        }

        // Calculate thumbnail size while keeping aspect ratio
        $ratio = min($thumbWidth / $width, $thumbHeight / $height);
        $newWidth = (int) ($width * $ratio);
        $newHeight = (int) ($height * $ratio);

        $thumbImage = imagecreatetruecolor($newWidth, $newHeight);

        // Preserve transparency for PNG and GIF
        if ($mime === 'image/png' || $mime === 'image/gif') {
            imagecolortransparent($thumbImage, imagecolorallocatealpha($thumbImage, 0, 0, 0, 127));
            imagealphablending($thumbImage, false);
            imagesavealpha($thumbImage, true);
        }

        imagecopyresampled($thumbImage, $sourceImage, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);

        // Save the thumbnail
        switch ($mime) {
            case 'image/jpeg':
                imagejpeg($thumbImage, $destPath, 90);
                break;
            case 'image/png':
                imagepng($thumbImage, $destPath);
                break;
            case 'image/gif':
                imagegif($thumbImage, $destPath);
                break;
        }

        imagedestroy($sourceImage);
        imagedestroy($thumbImage);
    }

    private function generate($engine, $imagePath, $prompt = null, $negative_prompt = null, $size = '1024x1024', $samples = 1)
    {
        Log::info('✅ Entered generate method', [
            'engine' => $engine,
            'imagePath' => $imagePath,
            'prompt' => $prompt
        ]);

        try {
            $credentials = is_string($engine->credentials)
                ? json_decode($engine->credentials)
                : $engine->credentials;

            $apiToken = $credentials->api_token ?? null;
            if (!$apiToken) {
                throw new Exception('Missing NanoBanana API token.');
            }

            $client = new Client();

            // Save uploaded image permanently
            $originalName = time() . '_' . $imagePath->getClientOriginalName();
            $savePath = public_path('image-image-uploads/' . $originalName);
            $imagePath->move(public_path('image-image-uploads/'), $originalName);

            Log::info('✅ Uploaded image saved permanently', ['path' => $savePath]);
            // Get MIME type
            $mime = mime_content_type($savePath); // e.g., image/png or image/jpeg

            // Create data URI for Gemini
            $imageData = 'data:' . $mime . ';base64,' . base64_encode(file_get_contents($savePath));


            // Send request to Replicate API
            $response = $client->post('https://api.replicate.com/v1/models/google/nano-banana/predictions', [
                'headers' => [
                    'Authorization' => 'Token ' . $apiToken,
                    'Content-Type' => 'application/json',
                ],
                'json' => [
                    'input' => [
                        'images' => [$imageData], // <-- use 'images' array for Gemini
                        'prompt' => $prompt,
                        'size' => $size,
                        'num_samples' => (int) $samples,
                        'strength' => 0.7,        // how much the prompt modifies the original
                    ],

                ],
                'timeout' => 120,
            ]);

            $responseData = json_decode($response->getBody(), true);
            $predictionId = $responseData['id'] ?? null;
            $status = $responseData['status'] ?? null;

            // Polling
            $maxRetries = 30;
            $retries = 0;
            $statusResponse = [];

            while (in_array($status, ['starting', 'processing']) && $retries < $maxRetries) {
                sleep(5);
                $statusResponse = $this->checkPredictionStatus($client, $apiToken, $predictionId);
                $status = $statusResponse['status'] ?? null;

                Log::info('NanoBanana Prediction status update', [
                    'retry' => $retries,
                    'status' => $status,
                ]);

                $retries++;
            }

            if ($status !== 'succeeded') {
                throw new Exception('Prediction failed. Final status: ' . ($status ?? 'unknown'));
            }

            if (!empty($statusResponse['output'])) {
                return $statusResponse['output'];
            }

            throw new Exception($statusResponse['error'] ?? 'Unknown error occurred.');
        } catch (RequestException $e) {
            if ($e->hasResponse()) {
                $error = (string) $e->getResponse()->getBody();
                Log::error('NanoBanana API error', ['response' => $error]);
                throw new Exception('Request Error: ' . $error);
            }

            throw new Exception('Request Error: ' . $e->getMessage());
        }
    }

    private function checkPredictionStatus(Client $client, $apiToken, $predictionId)
    {
        try {
            $response = $client->get("https://api.replicate.com/v1/predictions/{$predictionId}", [
                'headers' => [
                    'Authorization' => 'Token ' . $apiToken,
                ],
            ]);

            return json_decode($response->getBody(), true);
        } catch (RequestException $e) {
            throw new Exception('Status Check Error: ' . $e->getMessage());
        }
    }
}
