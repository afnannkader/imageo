<?php

use GuzzleHttp\Client;

function installer_trans($key)
{
    return $key;
}

function purchaseCodeValidation($purchaseCode, $alias)
{
    try {
        $client = new Client();
        $res = $client->get(config('system.license.api') . '?purchase_code=' . $purchaseCode . '&alias=' . strtolower($alias) . '&website=' . url('/'));
        if ($res->getStatusCode() == 200) {
            return json_decode($res->getBody());
        }
        return false;
    } catch (\Exception $e) {
        return false;
    }
}

function isInLiveServer()
{
    $locals = ['localhost', '127.0.0.1'];
    $host = parse_url(url('/'))['host'];
    if (in_array($host, $locals)) {
        return false;
    }
    return true;
}

function licenseType($type = null)
{
    $licenseType = config('system.system.license_type');
    if ($type) {
        return ($type == $licenseType) ? true : false;
    } else {
        return $licenseType;
    }
}

function extensionAvailability($name)
{
    if (!extension_loaded($name)) {
        $response = false;
    } else {
        $response = true;
    }
    return $response;
}

function phpExtensions()
{
    $extensions = [
        'BCMath',
        'Ctype',
        'Fileinfo',
        'JSON',
        'Mbstring',
        'OpenSSL',
        'PDO',
        'pdo_mysql',
        'Tokenizer',
        'XML',
        'cURL',
        'zip',
        'GD',
    ];
    return $extensions;
}

function filePermissionValidation($name)
{
    $perm = substr(sprintf('%o', fileperms($name)), -4);
    if ($perm >= '0775') {
        $response = true;
    } else {
        $response = false;
    }
    return $response;
}

function filePermissions()
{
    $filePermissions = [
        base_path('app/'),
        base_path('bootstrap/cache/'),
        base_path('database/'),
        base_path('lang/'),
        base_path('lang/en/'),
        base_path('storage/'),
        base_path('storage/app/'),
        base_path('storage/framework/'),
        base_path('storage/logs/'),
        'images/',
        'generation/images/',
        'generation/images/thumbnails/',
    ];
    return $filePermissions;
}

function currentStep($stepNumber)
{
    $steps = [
        'requirements' => 1,
        'permissions' => 2,
        'license' => 3,
        'database' => 4,
        'import' => 5,
        'complete' => 6,
    ];

    $step = $steps[request()->segment(2)];
    if ($step == $stepNumber) {
        return 'current';
    } elseif ($step > $stepNumber) {
        return 'active';
    }
}