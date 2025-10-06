<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\GeneratedImage;
use Illuminate\Http\Request;
use Validator;

class GalleryController extends Controller
{
    public function index()
    {
        $generatedImages = GeneratedImage::where('user_id', authUser()->id)
            ->notExpired();
        if (request()->has('search')) {
            $q = '%' . request()->input('search') . '%';
            $generatedImages->where('prompt', 'like', $q)
                ->orWhere('negative_prompt', 'like', $q);

        }
        $generatedImages = $generatedImages->orderByDesc('id')->paginate(20);
        $generatedImages->appends(['search' => request('search')]);
        $totalGeneratedImages = GeneratedImage::where('user_id', authUser()->id)->count();
        return view('user.gallery.index', ['generatedImages' => $generatedImages, 'totalGeneratedImages' => $totalGeneratedImages]);
    }

    public function update(Request $request, $id)
    {
        $generatedImage = GeneratedImage::where('user_id', authUser()->id)->where('id', unhashid($id))->notExpired()->firstOrFail();
        $validator = Validator::make($request->all(), [
            'visibility' => ['required', 'integer', 'min:0', 'max:1'],
        ]);
        if ($validator->fails()) {
            foreach ($validator->errors()->all() as $error) {
                return jsonError($error);
            }
        }
        $generatedImage->update(['visibility' => $request->visibility]);
        toastr()->success(lang('Updated successfully', 'gallery'));
        return back();
    }

    public function destroy($id)
    {
        $generatedImage = GeneratedImage::where('user_id', authUser()->id)->where('id', unhashid($id))->notExpired()->firstOrFail();
        $generatedImage->deleteResources();
        $generatedImage->delete();
        toastr()->success(lang('Deleted successfully', 'gallery'));
        return redirect()->route('user.gallery.index');
    }
}
