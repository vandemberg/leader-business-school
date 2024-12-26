<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

use App\Models\User;
use App\Models\Course;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->firstUser();
        $course = $this->firstCourse();
        $this->firstVideos($course);
    }

    private function firstUser(): void
    {
        User::factory()->create([
            'name' => 'Vandemberg Lima',
            'email' => 'vandemberg.silva.lima@gmail.com',
            'password' => bcrypt('secret')
        ]);
    }

    private function firstCourse(): Course
    {
        return Course::create([
            'title' => 'Maestria em Liderança',
            'description' => 'Aprenda a liderar com os melhores',
            'icon' => 'bx-chart',
            'thumbnail' => 'maestria_lider',
        ]);

    }

    private function firstVideos(Course $course): void
    {
        $course->modules()->create([
            'name' => 'Módulo 01 - Mentalidade Poderosa',
            'description' => 'Aprenda a ter uma mentalidade poderosa',
        ]);

        $module = $course->modules()->first();

        $module->videos()->createMany([
            [
                'title' => 'Aula 01 - Mentalidade Poderosa',
                'description' => 'A aula aborda tudo sobre a formação da mentalidade dos Líderes de sucesso',
                'url' => 'y6BkMrolHUM',
                'time_in_seconds' => '',
                'course_id' => $course->id,
            ],
            [
                'title' => 'Aula 02 - Inteligência Emocional',
                'description' => 'A aula aborda o que é Inteligência emocional, como construir ela e como ela impacta na sua liderança',
                'url' => 'Dmd-mAAG71c',
                'time_in_seconds' => '',
                'course_id' => $course->id,
            ],
            [
                'title' => 'Aula 03 - Princípios Poderosos',
                'description' => 'Nesse encontro, Jonatas traz 7 Princípios universais que Líderes devem viver na prática para se destacar como profissional',
                'url' => 'J5Dlx4E523E',
                'time_in_seconds' => '',
                'course_id' => $course->id,
            ],
            [
                'title' => 'Aula 04 - Alta Performance Pessoal',
                'description' => 'A aula traz conceitos e ferramentas para os Líderes terem muito mais equilíbrio e bem estar em suas vidas pessoais',
                'url' => 'MrOEQt01jI0',
                'time_in_seconds' => '',
                'course_id' => $course->id,
            ],
            [
                'title' => 'Aula 05 - Foco e Produtividade',
                'description' => 'A aula aborda como aumentarmos a nossa capacidade atencional, como isso reflete no nosso foco e ainda Jonatas traz 5 camadas para os líderes serem mais produtivos',
                'url' => 'RTjJN_fr1qQ',
                'time_in_seconds' => '',
                'course_id' => $course->id,
            ],
            [
                'title' => 'Aula 06 - Clima organizacional',
                'description' => 'Nessa aula, Jonatas traz tudo sobre como implementar uma cultura de felicidade corporativa no seu time ou empresa, e ainda traz um estudo sobre a importância de gerar 7 experiências transformadoras no seu time.',
                'url' => 'yqfp0KJaCA4',
                'time_in_seconds' => '',
                'course_id' => $course->id,
            ],
            [
                'title' => 'Aula 7 - Comunicação Assertiva para Líderes',
                'description' => 'Nesse encontro, trazemos como os líderes devem se comunicar com assertividade na sua vida profissional e que essa comunicação deve ser no que fala, no como fala e no comportamento não-verbal e ainda como se comunicar com cada perfil comportamental',
                'url' => 'jQz_0EXRh38',
                'time_in_seconds' => '',
                'course_id' => $course->id,
            ],
            [
                'title' => 'Aula 08 - Inovação tecnológica para líderes',
                'description' => 'Nesta aula, Jonatas trouxe um presente especial para os mentorados, um template do Notion para eles organizarem vida pessoal e profissional e ensinou como utilizar ele',
                'url' => 'oxz1bunjc0E',
                'time_in_seconds' => '',
                'course_id' => $course->id,
            ],
            [
                'title' => 'Aula 09 - Revisão dos 8 encontros',
                'description' => 'Neste encontro, Jonatas trouxe a revisão dos primeiros 8 encontros ao vivo da mentoria, passando apenas pelos pontos mais importantes de cada aula',
                'url' => 'uEhIFIWKLZc',
                'time_in_seconds' => '',
                'course_id' => $course->id,
            ],
            [
                'title' => 'Aula 10 - Matriz de decisão',
                'description' => 'Nesta aula, Jonatas abriu a aula para convidados e mostrou uma ferramenta poderosa para avaliação de desempenho dos colaboradores',
                'url' => 'kZ4oV0I2epU',
                'time_in_seconds' => '',
                'course_id' => $course->id,
            ],
        ]);
    }
}
