<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;


class Tanah extends Model
{
    use HasFactory;
    
    protected $table = 'tanahs';

    protected $fillable = [
        'Alamat',
        'jenis_tanah_id',
        'PH_Tanah',
        'Kelembaban_Tanah',
        'Suhu_Tanah',
        'Ketinggian_Tanah',
    ];

    public function jenisTanah()
    {
        return $this->belongsTo(JenisTanah::class, 'jenis_tanah_id');
    }

}
