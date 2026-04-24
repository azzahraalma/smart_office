<?php

namespace App\Models;

use CodeIgniter\Model;

class IdleLogModel extends Model
{
    protected $table = 'idle_logs';
    protected $primaryKey = 'id';
    protected $returnType = 'array';

    protected $allowedFields = [
        'user_id',
        'mulai',
        'selesai',
        'durasi'
    ];

    protected $useTimestamps = true;
}