<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\GeneratedImage;
use App\Models\StorageProvider;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class ImageController extends Controller
{
    public function index()
    {
        $generatedImages = GeneratedImage::public();

        if (request()->filled('search')) {
            $q = '%' . request()->input('search') . '%';
            $generatedImages->where('prompt', 'like', $q)
                ->orWhere('negative_prompt', 'like', $q);
        }

        $generatedImages = $generatedImages->notExpired()
            ->orderByDesc('id')
            ->paginate(settings('limits')->explore_page_images);

        return view('images.index', compact('generatedImages'));
    }

    public function generator(Request $request)
    {
        $ip = ipInfo()->ip;

        if (demoMode()) {
            return jsonError(admin_lang('This version is for demo purpose, generating images are not allowed.'));
        }

        if (!subscription()->is_subscribed) {
            return jsonError(lang('You need to have an active subscription to start generating the images', 'home page'));
        }

        $engine = engine($request->engine);
        if (!$engine) {
            return jsonError(lang('The selected engine is not active', 'home page'));
        }

        $storageProvider = StorageProvider::where('alias', env('FILESYSTEM_DRIVER'))->first();
        if (!$storageProvider) {
            return jsonError(lang('Storage provider error', 'home page'));
        }

        $validator = Validator::make($request->all(), [
            'prompt' => ['required', 'string'],
            'engine' => ['required', 'string'],
            'negative_prompt' => ['nullable', 'string'],
            'size' => ['required', 'in:' . str_replace(', ', ',', $engine->sizes)],
            'art_style' => ['nullable', 'in:' . str_replace(', ', ',', $engine->art_styles)],
            'lightning_style' => ['nullable', 'in:' . str_replace(', ', ',', $engine->lightning_styles)],
            'mood' => ['nullable', 'in:' . str_replace(', ', ',', $engine->moods)],
            'samples' => ['required', 'integer', 'min:1', 'max:' . min($engine->max, subscription()->plan->max_images)],
            'visibility' => ['sometimes', 'integer', 'min:0', 'max:1'],
        ]);

        if ($validator->fails()) {
            foreach ($validator->errors()->all() as $error) {
                return jsonError($error);
            }
        }

        if (!$engine->supportNegativePrompt()) {
            $request->negative_prompt = null;
        }

        if ($engine->filters) {
            foreach ($engine->getFiltersArray() as $word) {
                if (stripos($request->prompt, $word) !== false) {
                    return jsonError(lang('Your prompt contains forbidden words', 'home page'));
                }
            }
        }

        if (!in_array($engine->id, subscription()->plan->engines)) {
            return jsonError(lang('Invalid engine', 'home page'));
        }

        if (subscription()->remaining_images < $request->samples) {
            if (Auth::user()) {
                return jsonError(lang('You have exceeded the limit, please upgrade your plan', 'home page'));
            } else {
                return jsonError(lang('You have exceeded the limit, please register', 'home page'));
            }
        }

        if (subscription()->plan->expiration) {
            $expiryAt = Carbon::now()->addDays(subscription()->plan->expiration);
        } else {
            $expiryAt = null;
        }

        $prompt = $request->prompt;

        if ($request->filled('art_style')) {
            $prompt .= ', art style: ' . $request->art_style;
        }

        if ($request->filled('lightning_style')) {
            $prompt .= ', lightning style: ' . $request->lightning_style;
        }

        if ($request->filled('mood')) {
            $prompt .= ', mood: ' . $request->mood;
        }

        try {
            $handler = new $engine->handler;

            $generatedImages = $handler->process($engine, $prompt, $request->negative_prompt, $request->size, $request->samples, $storageProvider);
            if (!is_array($generatedImages)) {
                return jsonError($generatedImages);
            }
            Log::info($generatedImages);

            $images = [];
            foreach ($generatedImages as $key => $image) {
                $userId = authUser() ? authUser()->id : null;
                $request->visibility = !$userId ? 1 : $request->visibility;

                $generatedImage = GeneratedImage::create([
                    'user_id' => $userId,
                    'storage_provider_id' => $storageProvider->id,
                    'engine_id' => $engine->id,
                    'ip_address' => $ip,
                    'prompt' => $request->prompt,
                    'negative_prompt' => $request->negative_prompt,
                    'size' => $request->size,
                    'art_style' => $request->art_style,
                    'lightning_style' => $request->lightning_style,
                    'mood' => $request->mood,
                    'main' => $image['main'],
                    'thumbnail' => $image['thumbnail'],
                    'expiry_at' => $expiryAt,
                    'visibility' => $request->visibility,
                ]);

                if ($generatedImage) {
                    if (Auth::user()) {
                        Auth::user()->subscription->increment('generated_images');
                    }

                    $images[$key]['prompt'] = $generatedImage->prompt;
                    $images[$key]['src'] = $generatedImage->getThumbnailLink();
                    $images[$key]['link'] = route('images.show', hashid($generatedImage->id));
                    $images[$key]['download_link'] = route('images.download', [hashid($generatedImage->id), $generatedImage->getMainImageName()]);
                }
            }

            return response()->json(['images' => $images]);
        } catch (Exception $e) {
            return jsonError($e->getMessage());
        }
    }

    public function show($id)
    {
        $generatedImage = GeneratedImage::where('id', unhashid($id))->notExpired()->firstOrFail();
        Log::info($generatedImage);

        if ($generatedImage->isPrivate()) {
            abort_if(auth()->user() && auth()->user()->id != $generatedImage->user_id, 404);
            abort_if(!auth()->user() && $generatedImage->user_id, 404);
            abort_if(!auth()->user() && $generatedImage->ip != ipInfo()->ip, 404);
        }

        $generatedImage->increment('views');
        return view('images.show', ['generatedImage' => $generatedImage]);
    }

    public function download($id)
    {
        $generatedImage = GeneratedImage::where('id', unhashid($id))->notExpired()->firstOrFail();

        if ($generatedImage->isPrivate()) {
            abort_if(auth()->user() && auth()->user()->id != $generatedImage->user_id, 404);
            abort_if(!auth()->user() && $generatedImage->user_id, 404);
            abort_if(!auth()->user() && $generatedImage->ip != ipInfo()->ip, 404);
        }

        if (!$this->authorizedUrl(route('images.show', hashid($generatedImage->id)))) {
            return redirect()->route('images.show', hashid($generatedImage->id));
        }

        $response = $generatedImage->download();
        if (!$response) {
            toastr()->error(lang('Download Error', 'image page'));
            return back();
        }

        $generatedImage->increment('downloads');

        return $response;
    }

    private function authorizedUrl($url)
    {
        $referer = request()->server('HTTP_REFERER');
        if ($referer && filter_var($referer, FILTER_VALIDATE_URL) !== false) {
            $referer = parse_url($referer);
            $url = parse_url($url);
            if ($url['host'] == $referer['host']) {
                return true;
            }
        }
        return false;
    }
}