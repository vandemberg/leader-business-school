<?php

namespace App\Http\Controllers;

use Inertia\Inertia;
use App\Models\Teacher;

class TeachersController extends Controller
{
    public function index()
    {
        $teachers = Teacher::all();
        return Inertia::render('Teachers/Index')
            ->with('teachers', $teachers);
    }
}
