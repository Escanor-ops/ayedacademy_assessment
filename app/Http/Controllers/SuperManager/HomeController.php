<?php

namespace App\Http\Controllers\SuperManager;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    public function index(){
        return view('super.home.index');
    }
}
