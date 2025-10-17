<?php

namespace App\Http\Controllers;

use App\Engines\NanoBananaEngine;
use App\Models\GeneratedImage;
use App\Models\StorageProvider;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class ImageToImageController extends Controller
{
    public function index()
    {
        return view('images.image_to_image');
    }

    public function generate(Request $request)
    {
        if (demoMode()) {
            return response()->json(['status' => 'error', 'message' => admin_lang('This version is for demo purpose, generating images are not allowed.')], 422);
        }

        if (!subscription()->is_subscribed) {
            return response()->json(['status' => 'error', 'message' => lang('You need to have an active subscription to start generating the images', 'home page')], 422);
        }

        $validator = Validator::make($request->all(), [
            'prompt' => ['required', 'string'],
            'images' => ['nullable'],
            'images.*' => ['image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
            'engine' => ['nullable', 'string'],
            'output_format' => ['nullable', 'in:jpg,png,webp'],
        ]);

        if ($validator->fails()) {
            foreach ($validator->errors()->all() as $error) {
                return response()->json(['status' => 'error', 'message' => $error], 422);
            }
        }

        $outputFormat = $request->input('output_format', 'jpg');

        $ip = ipInfo()->ip;
        $storageProvider = StorageProvider::where('alias', env('FILESYSTEM_DRIVER'))->first();
        if (!$storageProvider) {
            return response()->json(['status' => 'error', 'message' => lang('Storage provider error', 'home page')], 422);
        }

        // Upload incoming images to temporary storage or use direct URLs if provided
        $imageUrls = [];
        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $file) {
                $path = $file->store('tmp/image2image', 'public');
                $imageUrls[] = asset('storage/' . $path);
            }
        } elseif (is_array($request->input('image_urls'))) {
            $imageUrls = array_values(array_filter($request->input('image_urls')));
        }

        $imageUrls = [];
        
        if (count($imageUrls) === 0) {
            // Testing defaults: public bedsheet images
            $imageUrls = [
                'https://replicate.delivery/pbxt/NbYIclp4A5HWLsJ8lF5KgiYSNaLBBT1jUcYcHYQmN1uy5OnN/tmpcqc07f_q.png',
                'https://replicate.delivery/pbxt/NbYId45yH8s04sptdtPcGqFIhV7zS5GTcdS3TtNliyTAoYPO/Screenshot%202025-08-26%20at%205.30.12%E2%80%AFPM.png',
            ];
        }

        try {
            $engineParam = $request->string('engine', 'replicate:nano-banana');

            switch ($engineParam) {
                case 'replicate:nano-banana':
                default:
                    $engine = new NanoBananaEngine();
                    $processed = $engine->process($imageUrls, $request->input('prompt'), $storageProvider, $outputFormat);
                    break;
            }

            if (!is_array($processed) || count($processed) === 0) {
                return response()->json(['status' => 'error', 'message' => lang('Generation failed', 'images')], 500);
            }

            $userId = authUser() ? authUser()->id : null;
            $visibility = !$userId ? 1 : (int) $request->input('visibility', 1);
            $expiryAt = subscription()->plan->expiration ? Carbon::now()->addDays(subscription()->plan->expiration) : null;

            $created = [];
            foreach ($processed as $img) {
                $generatedImage = GeneratedImage::create([
                    'user_id' => $userId,
                    'storage_provider_id' => $storageProvider->id,
                    'engine_id' => null,
                    'ip_address' => $ip,
                    'prompt' => $request->prompt,
                    'negative_prompt' => null,
                    'size' => 'custom',
                    'art_style' => null,
                    'lightning_style' => null,
                    'mood' => null,
                    'main' => $img['main'],
                    'thumbnail' => $img['thumbnail'] ?? null,
                    'expiry_at' => $expiryAt,
                    'visibility' => $visibility,
                ]);
                if ($generatedImage && Auth::user()) {
                    Auth::user()->subscription->increment('generated_images');
                }
                if ($generatedImage) {
                    // Attempt to mirror files to public path if local storage provider is used
                    if ($storageProvider->isLocal()) {
                        try {
                            $mainPath = $generatedImage->getMainImagePath();
                            $publicMain = public_path($mainPath);
                            if (!file_exists(dirname($publicMain))) {
                                @mkdir(dirname($publicMain), 0775, true);
                            }
                            if (!file_exists($publicMain)) {
                                if (Storage::disk('direct')->has($mainPath)) {
                                    $contents = Storage::disk('direct')->get($mainPath);
                                    @file_put_contents($publicMain, $contents);
                                }
                            }

                            if ($generatedImage->thumbnail) {
                                $thumbPath = $generatedImage->thumbnail->path ?? null;
                                if ($thumbPath) {
                                    $publicThumb = public_path($thumbPath);
                                    if (!file_exists(dirname($publicThumb))) {
                                        @mkdir(dirname($publicThumb), 0775, true);
                                    }
                                    if (!file_exists($publicThumb) && Storage::disk('direct')->has($thumbPath)) {
                                        $tcontents = Storage::disk('direct')->get($thumbPath);
                                        @file_put_contents($publicThumb, $tcontents);
                                    }
                                }
                            }
                        } catch (\Throwable $e) {
                            Log::warning('Public mirror failed: ' . $e->getMessage());
                        }
                    }
                    $created[] = $generatedImage;
                }
            }

            $first = $created[0] ?? null;
            return response()->json([
                'status' => 'success',
                'input_images' => $imageUrls,
                'output_image' => $first ? $first->getMainImageLink() : null,
                'view_link' => $first ? route('images.show', hashid($first->id)) : null,
            ]);
        } catch (\Throwable $e) {
            Log::error('ImageToImage generate error: ' . $e->getMessage());
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }
}


