<?php

namespace App\Domains\Owner\Events;
use App\Models\Course;
use App\Domains\Owner\Repositories\TaskRepository;
use App\Models\Task;

class RegisterCourseNextSteps
{
    public TaskRepository $taskRepository;

    public function __construct()
    {
        $this->taskRepository = new TaskRepository(new Task());
    }

    public function handle(object $event): void
    {
        $course = $event->course;

        $this->registerThumbnailTask($course);
        $this->regisgterFirstModuleTask($course);
    }

    public function regisgterFirstModuleTask($course): void
    {
        if ($course->modules()->count() > 0) {
            return;
        }

        $taskAttributes = [
            'name' => "[$course->title] - Crie o primeiro módulo",
            'description' => 'Todo curso precisa de pelo menos um módulo. Crie o primeiro módulo para que o curso possa ser publicado.',
            'origin' => 'register-course',
            'reason' => 'course-without-modules',
            'course_id' => $course->id,
            'responsible_id' => $course->responsible_id,
        ];

        $this->taskRepository->register($taskAttributes);
    }

    private function registerThumbnailTask($course): void
    {
        if ($course->thumbnail) {
            return;
        }

        $taskAttributes = [
            'name' => "[$course->title] - Adicionar Thumbnail",
            'description' => 'Todo curso tem que possuir uma Thumbanil!! Encontre uma thumbnail para o curso e adicione-a. Quando ela for adicionada a task será concluída sozinha',
            'origin' => 'register-course',
            'reason' => 'course-without-thumbnail',
            'course_id' => $course->id,
            'responsible_id' => $course->responsible_id,
        ];

        $this->taskRepository->register($taskAttributes);
    }
}
