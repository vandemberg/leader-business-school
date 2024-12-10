<?php
namespace App\Domains\Owner\Repositories;

use App\Domains\Owner\Repositories\ICourseRepository;

class CourseRepository implements ICourseRepository
{
    protected $model;

    public function __construct($course)
    {
        $this->model = $course;
    }

    public function register(array $data)
    {
        return $this->model->create($data);
    }
}
