<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Engine;
use App\Models\PaymentGateway;
use App\Models\Plan;
use Illuminate\Http\Request;
use Validator;

class PlanController extends Controller
{
    public function index()
    {
        $monthlyPlans = Plan::where('interval', 1)->get();
        $yearlyPlans = Plan::where('interval', 2)->get();
        return view('admin.plans.index', [
            'monthlyPlans' => $monthlyPlans,
            'yearlyPlans' => $yearlyPlans,
        ]);
    }

    public function create()
    {
        $engines = Engine::active()->get();
        return view('admin.plans.create', ['engines' => $engines]);
    }

    public function store(Request $request)
    {
        if (!$request->has('is_free')) {
            $activePaymentMethod = PaymentGateway::where('status', 1)->hasCurrency()->get();
            if (count($activePaymentMethod) < 1) {
                toastr()->error(admin_lang('No active payment method'))->info(admin_lang('Add your payment methods info before you start creating a plan'));
                return back()->withInput();
            }
        }

        $validator = Validator::make($request->all(), [
            'name' => ['required', 'string', 'max:255'],
            'short_description' => ['required', 'string', 'max:150'],
            'interval' => ['required', 'integer', 'min:1', 'max:2'],
            'price' => ['sometimes', 'required', 'numeric', 'regex:/^\d*(\.\d{2})?$/'],
            'images' => ['required', 'integer', 'min:1'],
            'max_images' => ['required', 'integer', 'min:1'],
            'expiration' => ['nullable', 'integer', 'min:1', 'max:3650'],
            'engines' => ['required', 'array'],
        ]);

        if ($validator->fails()) {
            foreach ($validator->errors()->all() as $error) {
                toastr()->error($error);
            }
            return back()->withInput();
        }

        foreach ($request->engines as $engine) {
            $engine = Engine::where('id', $engine)->active()->first();
            if (!$engine) {
                toastr()->error(admin_lang('One of selected engines are not active'));
                return back();
            }
        }

        if ($request->has('custom_features')) {
            foreach ($request->custom_features as $custom_feature) {
                if (empty($custom_feature['name'])) {
                    toastr()->error(admin_lang('Custom feature cannot be empty'));
                    return back()->withInput();
                }
            }
        }

        if ($request->has('is_free')) {
            $plan = Plan::free()->first();
            if ($plan) {
                toastr()->error(admin_lang('Free plan is already exists'));
                return back()->withInput();
            }
            $request->login_require = ($request->has('login_require')) ? 1 : 0;
            $request->price = 0;
            $request->is_free = 1;
        } else {
            $request->is_free = 0;
            $request->login_require = 1;
        }

        $request->is_featured = ($request->has('is_featured')) ? 1 : 0;
        $request->expiration = ($request->has('no_expiration')) ? null : $request->expiration;
        $request->advertisements = ($request->has('advertisements')) ? 1 : 0;
        if ($request->has('watermark')) {
            if (!settings('watermark')->status) {
                toastr()->error(admin_lang('Watermark is not enabled from settings'));
                return back()->withInput();
            }
            $request->watermark = 1;
        } else {
            $request->watermark = 0;
        }

        $plan = Plan::create([
            'name' => $request->name,
            'short_description' => $request->short_description,
            'interval' => $request->interval,
            'price' => $request->price,
            'images' => (int) $request->images,
            'max_images' => (int) $request->max_images,
            'expiration' => $request->expiration,
            'advertisements' => $request->advertisements,
            'watermark' => $request->watermark,
            'engines' => $request->engines,
            'custom_features' => $request->custom_features,
            'login_require' => $request->login_require,
            'is_free' => $request->is_free,
            'is_featured' => $request->is_featured,
        ]);

        if ($plan) {
            if ($request->has('is_featured')) {
                Plan::where([['id', '!=', $plan->id], ['interval', $plan->interval]])->update(['is_featured' => 0]);
            }
            toastr()->success(admin_lang('Created Successfully'));
            return redirect()->route('admin.plans.index');
        }
    }

    public function edit(Plan $plan)
    {
        $engines = Engine::active()->get();
        return view('admin.plans.edit', ['plan' => $plan, 'engines' => $engines]);
    }

    public function update(Request $request, Plan $plan)
    {
        $validator = Validator::make($request->all(), [
            'name' => ['required', 'string', 'max:255'],
            'short_description' => ['required', 'string', 'max:150'],
            'price' => ['sometimes', 'required', 'numeric', 'regex:/^\d*(\.\d{2})?$/'],
            'images' => ['required', 'integer', 'min:1'],
            'max_images' => ['required', 'integer', 'min:1'],
            'expiration' => ['nullable', 'integer', 'min:1', 'max:3650'],
            'engines' => ['required', 'array'],
        ]);

        if ($validator->fails()) {
            foreach ($validator->errors()->all() as $error) {
                toastr()->error($error);
            }
            return back()->withInput();
        }

        foreach ($request->engines as $engine) {
            $engine = Engine::where('id', $engine)->active()->first();
            if (!$engine) {
                toastr()->error(admin_lang('one of selected engines are not active'));
                return back();
            }
        }

        if ($request->has('custom_features')) {
            foreach ($request->custom_features as $custom_feature) {
                if (empty($custom_feature['name'])) {
                    toastr()->error(admin_lang('Custom feature cannot be empty'));
                    return back()->withInput();
                }
            }
        }

        if ($request->has('is_free')) {
            $freePlan = Plan::free()->first();
            if ($freePlan && $plan->id != $freePlan->id) {
                toastr()->error(admin_lang('Free plan is already exists'));
                return back()->withInput();
            }
            $request->login_require = ($request->has('login_require')) ? 1 : 0;
            $request->price = 0;
            $request->is_free = 1;
        } else {
            $request->is_free = 0;
            $request->login_require = 1;
        }

        $request->is_featured = ($request->has('is_featured')) ? 1 : 0;
        $request->expiration = ($request->has('no_expiration')) ? null : $request->expiration;
        $request->advertisements = ($request->has('advertisements')) ? 1 : 0;
        if ($request->has('watermark')) {
            if (!settings('watermark')->status) {
                toastr()->error(admin_lang('Watermark is not enabled from settings'));
                return back();
            }
            $request->watermark = 1;
        } else {
            $request->watermark = 0;
        }

        $update = $plan->update([
            'name' => $request->name,
            'short_description' => $request->short_description,
            'price' => $request->price,
            'images' => (int) $request->images,
            'max_images' => (int) $request->max_images,
            'expiration' => $request->expiration,
            'advertisements' => $request->advertisements,
            'watermark' => $request->watermark,
            'engines' => $request->engines,
            'custom_features' => $request->custom_features,
            'login_require' => $request->login_require,
            'is_free' => $request->is_free,
            'is_featured' => $request->is_featured,
        ]);

        if ($update) {
            if ($request->has('is_featured')) {
                Plan::where([['id', '!=', $plan->id], ['interval', $plan->interval]])->update(['is_featured' => 0]);
            }
            toastr()->success(admin_lang('Updated Successfully'));
            return back();
        }
    }

    public function destroy(Plan $plan)
    {
        if ($plan->subscriptions->count() > 0) {
            toastr()->error(admin_lang('Plan has subscriptions, you can delete them then delete the plan'));
            return back();
        }
        if ($plan->transactions->count() > 0) {
            toastr()->error(admin_lang('Plan has transactions, you can delete them then delete the plan'));
            return back();
        }
        $plan->delete();
        toastr()->success(admin_lang('Deleted successfully'));
        return back();
    }
}
