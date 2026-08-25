<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ScanLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'panitia_name',
        'panitia_pin',
        'peserta_nim',
        'peserta_nama',
        'peserta_prodi',
        'peserta_kursi',
        'status',
        'message',
        'scanned_at',
    ];

    protected $casts = [
        'scanned_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
