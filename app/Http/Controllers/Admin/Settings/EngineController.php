<?php

namespace App\Http\Controllers\Admin\Settings;

use App\Http\Controllers\Controller;
use App\Models\Engine;
use Illuminate\Http\Request;
use Validator;

class EngineController extends Controller
{
    public function index()
    {
        $engines = Engine::all();
        return view('admin.settings.engines.index', ['engines' => $engines]);
    }

    public function edit(Engine $engine)
    {
        return view('admin.settings.engines.edit', ['engine' => $engine]);
    }

    public function update(Request $request, Engine $engine)
    {
        $validator = Validator::make($request->all(), [
            'name' => ['required', 'string', 'max:255'],
            'sizes' => ['required'],
        ]);

        if ($validator->fails()) {
            foreach ($validator->errors()->all() as $error) {
                toastr()->error($error);
            }
            return back()->withInput();
        }

        foreach ($request->credentials as $key => $value) {
            if (!array_key_exists($key, (array) $engine->credentials)) {
                toastr()->error(admin_lang('Credentials parameter error'));
                return back();
            }
        }

        if ($request->has('status')) {
            foreach ($request->credentials as $key => $value) {
                if (empty($value)) {
                    toastr()->error(str_replace('_', ' ', $key) . admin_lang(' cannot be empty'));
                    return back();
                }
            }
            $request->status = Engine::STATUS_ACTIVE;
        } else {
            $request->status = Engine::STATUS_DISABLED;
        }

        $sizes = explode(',', $request->sizes);
        foreach ($sizes as $size) {
            if (!preg_match('/^\d+([x:]\d+)$/', $size)) {
                toastr()->error(admin_lang('The size format is invalid'));
                return back();
            }
        }

        $engine->name = $request->name;
        $engine->credentials = $request->credentials;
        $engine->filters = $request->filters;
        $engine->sizes = $request->sizes;
        $engine->art_styles = $request->art_styles;
        $engine->lightning_styles = $request->lightning_styles;
        $engine->moods = $request->moods;
        $engine->status = $request->status;
        $engine->save();

        toastr()->success(admin_lang('Updated Successfully'));
        return back();
    }
}
