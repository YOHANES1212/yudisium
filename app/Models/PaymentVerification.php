<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PaymentVerification extends Model
{
    use HasFactory;

    protected $fillable = [
        'nim',
        'status_pembayaran',
        'nomor_kursi',
        'validated_by',
        'validated_at',
        'waktu_kehadiran',
    ];

    protected $casts = [
        'validated_at' => 'datetime',
    ];
}
