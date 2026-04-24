<?php

namespace App\Models;

use CodeIgniter\Model;

class UserModel extends Model
{
    protected $table = 'users';
    protected $primaryKey = 'id';
    protected $returnType = 'array';
    protected $allowedFields = [
        'nama',
        'email',
        'password',
        'role',
        'foto',
        'status'
    ];

    protected $useTimestamps = true;
}