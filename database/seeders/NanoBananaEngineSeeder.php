<?php

namespace Database\Seeders;

use App\Models\Engine;
use Illuminate\Database\Seeder;

/**
 * NanoBananaEngineSeeder - Adds the Nano Banana engine to the database
 * 
 * This seeder creates a proper database entry for the Nano Banana engine
 * so it can be managed like other engines in the system.
 */
class NanoBananaEngineSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Check if Nano Banana engine already exists
        $existingEngine = Engine::where('alias', 'replicate:nano-banana')->first();
        
        if (!$existingEngine) {
            Engine::create([
                'name' => 'Replicate - Nano Banana',
                'alias' => 'replicate:nano-banana',
                'logo' => 'images/engines/nano-banana.png', // You can add a logo later
                'handler' => 'App\\Engines\\NanoBananaEngine',
                'credentials' => json_encode([
                    'api_token' => env('REPLICATE_API_TOKEN'),
                    'output_format' => 'jpg',
                ]),
                'instructions' => 'Upload one or more images and provide a text prompt to transform them using AI. This engine specializes in image-to-image generation.',
                'filters' => null, // No content filtering
                'support_negative_prompt' => false, // Nano Banana doesn't support negative prompts
                'sizes' => 'custom', // Custom size for image-to-image
                'art_styles' => null, // No art styles
                'lightning_styles' => null, // No lightning styles
                'moods' => null, // No moods
                'max' => 4, // Maximum 4 samples per generation
                'status' => 1, // Active
            ]);
            
            $this->command->info('Nano Banana engine added to database successfully!');
        } else {
            $this->command->info('Nano Banana engine already exists in database.');
        }
    }
}
