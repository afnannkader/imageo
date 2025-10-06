<?php

namespace App\Console\Commands;

use App\Models\GeneratedImage;
use Illuminate\Console\Command;

class DeleteExpiredImages extends Command
{
    protected $signature = 'images:delete-expired';

    protected $description = 'Delete expired images';

    public function handle()
    {
        $generatedImages = GeneratedImage::expired()->get();
        foreach ($generatedImages as $generatedImage) {
            $handler = $generatedImage->storageProvider->handler;
            $delete = $handler::delete($generatedImage->path);
            $generatedImage->delete();
        }
    }
}