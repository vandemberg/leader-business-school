<?php
namespace App\Domains\Owner\Repositories;

use App\Domains\Owner\Repositories\ITaskRepository;

class TaskRepository implements ITaskRepository
{
    protected $model;

    public function __construct($task)
    {
        $this->model = $task;
    }

    public function register(array $data)
    {
        return $this->model->create($data);
    }

    public function completeTask($taskId)
    {
        return $this->model->find($taskId)->update(['status' => 'completed']);
    }
}
