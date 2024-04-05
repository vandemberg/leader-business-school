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
            ],
            [
                'id' => 2,
                'name' => 'Pedro Almeida',
                'email' => 'pedro.almeida@gmail.com',
                'skills' => 'Palestrante Motivacional e Coach de Liderança Inspiradora',
                'bio' => 'Pedro é um palestrante motivacional e coach de liderança que inspira líderes a alcançarem novos patamares de excelência. Com uma abordagem dinâmica e envolvente, ele compartilha histórias inspiradoras e insights práticos para ajudar os participantes a descobrirem seu propósito, liderarem com paixão e transformarem suas organizações.',
            ],
            [
                'id' => 3,
                'name' => 'Ana Santos',
                'skills' => 'Treinadora Executiva e Mentora de Liderança Feminina',
                'bio' => 'Ana é uma treinadora executiva e mentora especializada em liderança feminina, com uma paixão por capacitar mulheres a alcançarem seu pleno potencial como líderes. Com uma abordagem centrada na confiança, ela trabalha com suas clientes para desenvolverem habilidades de liderança autênticas, construírem redes sólidas e superarem os desafios únicos que enfrentam no ambiente corporativo.',
            ],
            [
                'id' => 4,
                'name' => 'Carolina Mendes',
                'skills' => 'Facilitadora de Desenvolvimento de Equipe e Liderança Colaborativa',
                'bio' => 'Carolina é uma facilitadora de desenvolvimento de equipe especializada em liderança colaborativa e construção de relações interpessoais sólidas. Com uma abordagem baseada na colaboração e na inclusão, ela ajuda equipes a desenvolverem uma cultura de confiança e cooperação, onde cada membro se sente valorizado e capacitado a contribuir para o sucesso coletivo.',
            ],
            [
                'id' => 5,
                'name' => 'Ricardo Oliveira',
                'skills' => 'Consultor de Liderança e Gestão de Mudanças',
                'bio' => 'João é um consultor experiente em liderança e gestão de mudanças, com uma sólida formação acadêmica e mais de 15 anos de experiência prática. Ele tem ajudado empresas a navegar por períodos de transição, desenvolvendo estratégias de liderança eficazes e capacitando líderes a inspirar suas equipes para o sucesso em meio à mudança.',
            ],
            [
                'id' => 6,
                'name' => 'Carolina Mendes',
                'skills' => 'Facilitadora de Desenvolvimento de Equipe e Liderança Colaborativa',
                'bio' => 'Carolina é uma facilitadora de desenvolvimento de equipe especializada em liderança colaborativa e construção de relações interpessoais sólidas. Com uma abordagem baseada na colaboração e na inclusão, ela ajuda equipes a desenvolverem uma cultura de confiança e cooperação, onde cada membro se sente valorizado e capacitado a contribuir para o sucesso coletivo.',
            ],
        ];

        return Inertia::render('Teachers/Index')
            ->with('teachers', $teachers);
    }
}
