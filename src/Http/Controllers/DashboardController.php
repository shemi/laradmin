<?php

namespace Shemi\Laradmin\Http\Controllers;

class DashboardController extends Controller
{

    public function index()
    {
        return view('laradmin::dashboard');
    }

}