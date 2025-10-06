<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Cookie;

class ExtraController extends Controller
{
    public function cookie()
    {
        Cookie::queue('cookie_accepted', true, 1440 * 60);
    }

    public function popup()
    {
        Cookie::queue('popup_closed', true, 1440 * 60);
    }
}
