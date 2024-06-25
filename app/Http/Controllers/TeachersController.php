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
                'skills' => 'Consultor, Treinador e Founder da Leader in Company',
                'bio' => 'Jonatas é consultor empresarial e Treinador comportamental. Traz consigo uma vasta experiência em Neurociência, Marketing, Liderança, Comunicação, Produtividade e Comportamento humano. Com mais de uma década de atuação em prestigiadas multinacionais, Jonatas tem dedicado os últimos seis anos ao treinamento de pessoas, e agora concentra seus esforços no desenvolvimento de líderes e equipes. Seu objetivo atual é criar e aprimorar líderes para um novo mercado, caracterizado por profissionais mais eficazes e positivos.',
                'photo' => 'https://randomuser.me/api'
            ],
        ];

        return Inertia::render('Teachers/Index')
            ->with('teachers', $teachers);
    }
}
