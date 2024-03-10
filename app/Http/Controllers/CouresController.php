<?php

namespace App\Http\Controllers;

use Inertia\Inertia;

class CouresController extends Controller
{
    public function index()
    {
        return Inertia::render('Courses/Index');
    }
}
