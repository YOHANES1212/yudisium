<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Peserta extends Model
{
    use HasFactory;

    protected $table = 'peserta';

    protected $fillable = [
        'nim',
        'nama',
        'email',
        'prodi',
        'no_hp',
        'motto',
        'qr_code_path',
        'status_hadir',
        'waktu_hadir'
    ];
}
