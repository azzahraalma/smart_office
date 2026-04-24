<?php

namespace App\Models;

use CodeIgniter\Model;

class TaskFileModel extends Model
{
    protected $table      = 'task_files';
    protected $primaryKey = 'id';

    protected $allowedFields = [
        'task_id',
        'user_id',
        'file_name',
        'original_name',
    ];

    protected $useTimestamps = true;

    public function getByTask($taskId)
    {
        return $this->where('task_id', $taskId)
                    ->orderBy('created_at', 'DESC')
                    ->findAll();
    }
}