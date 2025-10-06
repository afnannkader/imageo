<?php

namespace App\Http\Controllers\Admin\System;

use App\Http\Controllers\Controller;
use App\Models\Settings;
use Illuminate\Http\Request;
use Validator;

class MaintenanceController extends Controller
{
    public function index()
    {
        return view('admin.system.maintenance');
    }

    public function update(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'maintenance.title' => ['required_if:maintenance.status,on', 'nullable', 'string', 'max:150'],
            'maintenance.body' => ['required_if:maintenance.status,on', 'nullable', 'string', 'max:500'],
        ]);

        if ($validator->fails()) {
            foreach ($validator->errors()->all() as $error) {
                toastr()->error($error);
            }
            return back();
        }

        $requestData = $request->except('_token');
        $maintenance = $requestData['maintenance'];

        $maintenance['status'] = ($request->has('maintenance.status')) ? 1 : 0;

        $update = Settings::updateSettings('maintenance', $maintenance);
        if (!$update) {
            toastr()->error(admin_lang('Updated Error'));
            return back();
        }

        toastr()->success(admin_lang('Updated Successfully'));
        return back();
    }
}
