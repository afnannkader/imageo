<?php

namespace App\Engines;

use App\Traits\InteractWithImageGeneration;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Log;

/**
 * NanoBananaEngine - Handles image-to-image generation using Replicate's Nano Banana model
 * 
 * This engine class follows the same pattern as other engines in the system:
 * 1. Accepts engine configuration and parameters like other engines
 * 2. Makes API calls to Replicate's Nano Banana model
 * 3. Processes the response and downloads generated images
 * 4. Stores images using the application's storage system
 * 
 * The Nano Banana model takes input images and a text prompt to generate new images
 * in a specific style or with modifications based on the prompt.
 */
class NanoBananaEngine
{
    use InteractWithImageGeneration;

    /**
     * Main process method - follows the same signature as other engines
     * 
     * @param object $engine Engine configuration object
     * @param string $prompt Text description of how to transform the images
     * @param string|null $negative_prompt Not used in image-to-image (always null)
     * @param string $size Not used in image-to-image (always 'custom')
     * @param int $samples Number of images to generate
     * @param object $storageProvider Storage provider instance
     * @return array Processed images with storage metadata or error message
     */
    public function process($engine, $prompt, $negative_prompt = null, $size, $samples, $storageProvider)
    {
        try {
            // Get input images from the request (stored in engine data or use defaults)
            $imageUrls = $this->getInputImages($engine);
            
            // Generate images using Replicate API
            $generatedImages = $this->generate($engine, $prompt, $imageUrls, $samples);
            
            $result = [];
            if (is_array($generatedImages)) {
                foreach ($generatedImages as $key => $image) {
                    $image = $this->downloadImage($image);
                    $result[$key] = $this->imageProcess($image, $storageProvider);
                }
            } else {
                return $generatedImages; // Return error message
            }
            
            return $result;
        } catch (Exception $e) {
            return $e->getMessage();
        }
    }

    /**
     * Generate images using Replicate Nano Banana API
     * 
     * @param object $engine Engine configuration object
     * @param string $prompt Text description for image transformation
     * @param array $imageUrls Input image URLs
     * @param int $samples Number of images to generate
     * @return array Array of generated image URLs or error message
     */
    private function generate($engine, $prompt, $imageUrls, $samples)
    {
        try {
            // Get API token from engine credentials or environment
            $apiToken = $engine->credentials->api_token ?? env('REPLICATE_API_TOKEN');
            
            // Create HTTP client for making API requests
            $client = new Client();
            
            // Make POST request to Replicate's Nano Banana model endpoint
            $response = $client->post('https://api.replicate.com/v1/models/google/nano-banana/predictions', [
                'headers' => [
                    'Authorization' => 'Bearer ' . $apiToken, // API authentication
                    'Content-Type' => 'application/json',
                    'Prefer' => 'wait', // Wait for completion instead of async processing
                ],
                'json' => [
                    'input' => [
                        'prompt' => $prompt, // Text description for image transformation
                        'image_input' => array_values($imageUrls), // Input images to transform
                        'output_format' => $engine->credentials->output_format ?? 'jpg', // Output format from engine config
                    ],
                ],
                'timeout' => 180, // 3 minutes timeout for generation
            ]);

            // Parse the JSON response from Replicate
            $body = (string) $response->getBody();
            $decoded = json_decode($body, true) ?: [];
            
            // Log the API response for debugging purposes
            Log::info(['nano_banana_api_response' => $decoded]);
            
            // Extract image URLs from response
            if (isset($decoded['output'])) {
                if (is_array($decoded['output'])) {
                    return $decoded['output'];
                } elseif (is_string($decoded['output'])) {
                    return [$decoded['output']];
                }
            }
            
            return 'No images generated from API response';
        } catch (Exception $e) {
            throw new Exception($e->getMessage());
        }
    }

    /**
     * Get input images from engine data
     * 
     * @param object $engine Engine configuration object
     * @return array Array of input image URLs
     */
    private function getInputImages($engine)
    {
        // Get images from engine data (processed by ImageController)
        if (isset($engine->input_images) && is_array($engine->input_images)) {
            return $engine->input_images;
        }
        
        // If no images provided, return empty array (ImageController should handle fallbacks)
        return [];
    }
}


