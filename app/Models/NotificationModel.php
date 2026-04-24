<?php

namespace App\Models;

use CodeIgniter\Model;

class NotificationModel extends Model
{
    protected $table = 'notifications';
    protected $primaryKey = 'id';
    protected $returnType = 'array';

    protected $allowedFields = [
        'user_id',
        'judul',
        'pesan',
        'tipe',
        'is_read',
        'created_at',
        'updated_at'
    ];
    protected $useTimestamps = true;
}