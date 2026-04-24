<?php

namespace App\Models;

use CodeIgniter\Model;

class BreakLogModel extends Model
{
    protected $table = 'break_logs';
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