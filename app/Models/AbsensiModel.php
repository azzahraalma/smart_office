<?php

namespace App\Models;

use CodeIgniter\Model;

class AbsensiModel extends Model
{
    protected $table            = 'absensi';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $protectFields    = true;

    protected $allowedFields = [
        'user_id',
        'nama',
        'tanggal',
        'jam_masuk',
        'jam_keluar',
        'status',
        'lokasi_masuk',
        'lokasi_keluar',
        'keterangan',
        'latitude_masuk',
        'longitude_masuk',
        'latitude_keluar',
        'longitude_keluar',
        'approved_by',
        'approved_at',
        'approval_status',
        'is_overtime',      
        'overtime_minutes',
    ];

    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
}
