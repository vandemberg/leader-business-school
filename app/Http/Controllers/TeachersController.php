<?php

namespace App\Http\Controllers;

use Inertia\Inertia;

class TeachersController extends Controller
{
    public function index()
    {
        $teachers = [
            [
                'id' => 1,
                'name' => 'Jonatas Lucas',
                'email' => 'jonatas.lucas@gmail.com',
                'skills' => 'Consultor, Instrutor e Founder da Leader in Company',
                'bio' => 'Jonatas com mais de 10 anos em posições de liderança em empresas renomadas, Jonatas Lucas decidiu se dedicar ao ensino do que faz um líder. Atualmente é consultor, instrutor e founder da Leader in Company.',
                'photo' => 'https://randomuser.me/api'
            ],
        ];

        return Inertia::render('Teachers/Index')
            ->with('teachers', $teachers);
    }
}
