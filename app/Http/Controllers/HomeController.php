<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Engine;
use App\Models\GeneratedImage;

class HomeController extends Controller
{
    public function index()
    {
        $generatedImages = GeneratedImage::public()
            ->notExpired()
            ->limit(settings('limits')->home_page_images)
            ->orderbyDesc('id')->get();

        $engines = null;
        if (subscription() && subscription()->is_subscribed && subscription()->plan->engines) {
            $engines = Engine::whereIn('id', subscription()->plan->engines)
                ->active()->get();
        }

        return view('home', [
            'generatedImages' => $generatedImages,
            'engines' => $engines,
        ]);
    }
}
