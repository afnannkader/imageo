<?php

namespace Vironeer\Installer\App\Http\Controllers;

use App\Models\Admin;
use App\Models\Settings;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Str;
use Validator;

class InstallController extends Controller
{
    public function redirect()
    {
        return redirect()->route('install.requirements');
    }

    public function requirements()
    {
        if (config('system.install.requirements')) {
            return redirect()->route('install.permissions');
        }

        $error = false;
        if (in_array(false, $this->extensionsArray())) {
            $error = true;
        }

        return view('vironeer::requirements', [
            'extensions' => phpExtensions(),
            'error' => $error,
        ]);
    }

    public function requirementsAction(Request $request)
    {
        if (in_array(false, $this->extensionsArray())) {
            return redirect()->route('install.requirements');
        }

        Artisan::call('key:generate');
        setEnv('APP_ENV', 'production');
        setEnv('VR_REQUIREMENTS', 1);

        return redirect()->route('install.permissions');
    }

    public function permissions()
    {
        if (config('system.install.file_permissions')) {
            return redirect()->route('install.license');
        }

        if (!config('system.install.requirements')) {
            return redirect()->route('install.requirements');
        }

        $error = false;
        if (in_array(false, $this->permissionsArray())) {
            $error = true;
        }

        return view('vironeer::permissions', ['permissions' => filePermissions(), 'error' => $error]);
    }

    public function permissionsAction(Request $request)
    {
        if (in_array(false, $this->permissionsArray())) {
            return redirect()->route('install.permissions');
        }

        setEnv('VR_FILEPERMISSIONS', 1);
        return redirect()->route('install.license');
    }

    public function license()
    {
        if (config('system.install.license')) {
            return redirect()->route('install.database.details');
        }

        if (!config('system.install.file_permissions')) {
            return redirect()->route('install.requirements');
        }

        return view('vironeer::license');
    }

    public function licenseAction(Request $request)
    {
        if (empty($request->purchase_code)) {
            return redirect()->back()->withErrors(['Purchase code is required']);
        }
        if (!preg_match("/^([a-f0-9]{8})-(([a-f0-9]{4})-){3}([a-f0-9]{12})$/i", $request->purchase_code)) {
            if (!preg_match("/^([d-u0-9]{10})-(([d-u0-9]{5})-){3}([d-u0-9]{10})$/i", $request->purchase_code)) {
                return redirect()->back()->withErrors(['Invalid purchase code']);
            }
        }
        if (isInLiveServer()) {
            try {
                $response = purchaseCodeValidation($request->purchase_code, config('system.item.alias'));
                if (isset($response->status)) {
                    if ($response->status == "success") {
                        setEnv('SYSTEM_LICENSE_TYPE', $response->data->license_type);
                        setEnv('VR_LICENSE', 1);
                        return redirect()->route('install.database.details');
                    } elseif ($response->status == "error") {
                        return redirect()->back()->withErrors([$response->message]);
                    }
                } else {
                    return redirect()->back()->withErrors(['Connection error, please try again later']);
                }
            } catch (Exception $e) {
                return redirect()->back()->withErrors(['Connection error, please try again later']);
            }
        } else {
            setEnv('VR_LICENSE', 1);
            return redirect()->route('install.database.details');
        }
    }

    public function databaseDetails()
    {
        if (config('system.install.database_info')) {
            return redirect()->route('install.database.import');
        }

        if (!config('system.install.license')) {
            return redirect()->route('install.license');
        }

        return view('vironeer::database.details');
    }

    public function databaseDetailsAction(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'db_host' => ['required', 'string'],
            'db_name' => ['required', 'string'],
            'db_user' => ['required', 'string'],
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        if (str_contains($request->db_host, '#') || str_contains($request->db_name, '#') || str_contains($request->db_user, '#')) {
            return redirect()->back()->withErrors(['Database details cannot contain a hashtag #'])->withInput();
        }

        try {
            if (!function_exists('curl_version')) {
                return redirect()->back()->withErrors(['CURL does not exist in server'])->withInput();
            }
            if (!is_writable(base_path('.env'))) {
                return redirect()->back()->withErrors(['.env file is not writable'])->withInput();
            }
            if (!@mysqli_connect($request->db_host, $request->db_user, $request->db_pass, $request->db_name)) {
                return redirect()->back()->withErrors(['Incorrect database details'])->withInput();
            }
            setEnv('DB_HOST', $request->db_host);
            setEnv('DB_DATABASE', $request->db_name);
            setEnv('DB_USERNAME', $request->db_user);
            setEnv('DB_PASSWORD', $request->db_pass, true);
            setEnv('VR_DATABASEINFO', 1);
            return redirect()->route('install.database.import');
        } catch (Exception $e) {
            return redirect()->back()->withErrors([$e->getMessage()])->withInput();
        }
    }

    public function databaseImport()
    {
        if (config('system.install.database_import')) {
            return redirect()->route('install.complete');
        }

        if (!config('system.install.database_info')) {
            return redirect()->route('install.database.details');
        }

        return view('vironeer::database.import');
    }

    public function databaseImportAction(Request $request)
    {
        if (!file_exists(base_path('database/sql/data.sql'))) {
            return redirect()->back()->withErrors(['SQL file is missing ' . base_path('database/sql/data.sql')])->withInput();
        }
        try {
            DB::connection()->getPdo();
            if (DB::connection()->getDatabaseName()) {
                $sql = base_path('database/sql/data.sql');
                $import = DB::unprepared(file_get_contents($sql));
                if ($import) {
                    setEnv('VR_DATABASEIMPORT', 1);
                    return redirect()->route('install.complete');
                }
            } else {
                return redirect()->back()->withErrors(['Could not find the database. Please check your configuration.']);
            }
        } catch (Exception $e) {
            return redirect()->back()->withErrors([$e->getMessage()]);
        }
    }

    public function databaseImportDownload(Request $request)
    {
        $sql = base_path('database/sql/data.sql');
        if (!file_exists($sql)) {
            return redirect()->back()->withErrors(['SQL file is missing ' . base_path('database/sql/data.sql')])->withInput();
        }
        return response()->download($sql);
    }

    public function databaseImportSkip(Request $request)
    {
        setEnv('VR_DATABASEIMPORT', 1);
        return redirect()->route('install.complete');
    }

    public function complete()
    {
        if (!config('system.install.database_import')) {
            return redirect()->route('install.database.import');
        }

        return view('vironeer::complete');
    }

    public function completeAction(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'website_name' => ['required', 'string', 'max:200'],
            'website_url' => ['required', 'url'],
            'admin_path' => ['required', 'string', 'alpha_num'],
            'email' => ['required', 'string', 'email', 'unique:admins'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        if (str_contains($request->website_url, '#')) {
            return redirect()->back()->withErrors(['Website URL cannot contain a hashtag #'])->withInput();
        }

        $avatar = 'images/avatars/default.png';
        $createAdmin = Admin::create([
            'name' => 'Admin Admin',
            'firstname' => 'Admin',
            'lastname' => 'Admin',
            'email' => $request->email,
            'password' => bcrypt($request->password),
            'avatar' => $avatar,
        ]);

        if ($createAdmin) {
            $generalSettings = Settings::selectSettings('general');
            $generalSettings->site_name = $request->website_name;
            $generalSettings->site_url = $request->website_url;
            $update = Settings::updateSettings('general', $generalSettings);
            if ($update) {
                setEnv('APP_NAME', Str::slug($request->website_name, '_'));
                setEnv('APP_URL', $request->website_url);
                setEnv('APP_ADMIN', $request->admin_path, true);
                setEnv('VR_COMPLETE', 1);
                return redirect(url($request->admin_path));
            } else {
                return redirect()->back()->withErrors(['Failed to update general settings'])->withInput();
            }
        }
    }

    public function completeBack(Request $request)
    {
        setEnv('VR_DATABASEIMPORT', '');
        return redirect()->route('install.database.import');
    }

    private function extensionsArray()
    {
        $extensionsArray = [];
        foreach (phpExtensions() as $extension) {
            $extensionsArray[] = extensionAvailability($extension);
        }
        return $extensionsArray;
    }

    private function permissionsArray()
    {
        $permissions = filePermissions();
        $permissionsArray = [];
        foreach ($permissions as $permission) {
            $permissionsArray[] = filePermissionValidation($permission);
        }
        return $permissionsArray;
    }
}
