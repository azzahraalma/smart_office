<?php

namespace App\Models;

use CodeIgniter\Model;

class KantorConfigModel extends Model
{
    protected $table = 'kantor_config';

    protected $allowedFields = [
        'nama_kantor',
        'latitude',
        'longitude',
        'radius_meter',
        'allowed_ip',
        'jam_masuk',
        'jam_keluar',
        'maks_break_menit',
    ];

    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';

    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
}