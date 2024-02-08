<?php

namespace App\Http\Controllers;

use Inertia\Inertia;

class HomeController extends Controller
{
    /**
     * Display Home page.
     */
    public function index()
    {
        return Inertia::render('Home');
    }
}
