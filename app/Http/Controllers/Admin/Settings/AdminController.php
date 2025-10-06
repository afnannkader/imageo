<?php

namespace App\Http\Controllers\Admin\Settings;

use App\Http\Controllers\Controller;
use App\Models\Admin;
use App\Models\BlogArticle;
use Illuminate\Http\Request;
use Str;
use Validator;

class AdminController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $admins = Admin::where('id', '!=', authAdmin()->id)->get();
        return view('admin.settings.admins.index', ['admins' => $admins]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $password = Str::random(15);
        return view('admin.settings.admins.create', ['password' => $password]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'avatar' => ['image', 'mimes:png,jpg,jpeg'],
            'firstname' => ['required', 'string', 'max:255'],
            'lastname' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'unique:admins'],
            'password' => ['required', 'min:8'],
        ]);
        if ($validator->fails()) {
            foreach ($validator->errors()->all() as $error) {
                toastr()->error($error);
            }
            return back()->withInput();
        }
        if ($request->has('avatar')) {
            $uploadAvatar = imageUpload($request->file('avatar'), 'images/avatars/admins/', '110x110');
        } else {
            $uploadAvatar = 'images/avatars/default.png';
        }
        $create = Admin::create([
            'firstname' => $request->firstname,
            'lastname' => $request->lastname,
            'avatar' => $uploadAvatar,
            'email' => $request->email,
            'password' => bcrypt($request->password),
        ]);
        if ($create) {
            toastr()->success(admin_lang('Created Successfully'));
            return redirect()->route('admin.settings.admins.index');
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Admin  $admin
     * @return \Illuminate\Http\Response
     */
    public function show(Admin $admin)
    {
        return abort(404);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Admin  $admin
     * @return \Illuminate\Http\Response
     */
    public function edit(Admin $admin)
    {
        if ($admin->id == authAdmin()->id) {
            return abort(404);
        }
        return view('admin.settings.admins.edit', ['admin' => $admin]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Admin  $admin
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Admin $admin)
    {
        if ($admin->id == authAdmin()->id) {
            toastr()->error(admin_lang('Something went wrong please try again'));
            return back()->withInput();
        }
        $validator = Validator::make($request->all(), [
            'avatar' => ['image', 'mimes:png,jpg,jpeg'],
            'firstname' => ['required', 'string', 'max:255'],
            'lastname' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'unique:admins,email,' . $admin->id],
        ]);
        if ($request->has('password') && $request->password != null) {
            $validator = Validator::make($request->all(), [
                'password' => ['min:8'],
            ]);
            $password = bcrypt($request->password);
        } else {
            $password = $admin->password;
        }
        if ($validator->fails()) {
            foreach ($validator->errors()->all() as $error) {
                toastr()->error($error);
            }
            return back();
        }
        if ($request->has('avatar')) {
            if ($admin->avatar == 'images/avatars/default.png') {
                $uploadAvatar = imageUpload($request->file('avatar'), 'images/avatars/admins/', '110x110');
            } else {
                $uploadAvatar = imageUpload($request->file('avatar'), 'images/avatars/admins/', '110x110', null, $admin->avatar);
            }
        } else {
            $uploadAvatar = $admin->avatar;
        }
        $update = $admin->update([
            'firstname' => $request->firstname,
            'lastname' => $request->lastname,
            'avatar' => $uploadAvatar,
            'email' => $request->email,
            'password' => $password,
        ]);
        if ($update) {
            toastr()->success(admin_lang('Updated Successfully'));
            return back();
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Admin  $admin
     * @return \Illuminate\Http\Response
     */
    public function destroy(Admin $admin)
    {
        $articles = BlogArticle::where('admin_id', $admin->id)->get();
        if ($articles->count() >= 1) {
            foreach ($articles as $article) {
                removeFile($article->image);
            }
        }
        if ($admin->avatar != 'images/avatars/default.png') {
            removeFile($admin->avatar);
        }
        $admin->delete();
        toastr()->success(admin_lang('Deleted Successfully'));
        return back();
    }
}
