<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;


class Gambar extends Model
{
    protected $table = 'tbl_gambar';
    protected $primaryKey = 'id_gambar';
    
    public function statik()
    {
        return $this->belongsTo('App\Models\Statik', 'id_konten');
    }

    public function getImageAttribute($value)
    {
        return "https://jdih.perpusnas.go.id/files/media/" . $value;
    }

    public function getImagethumbAttribute($value)
    {
        return "https://jdih.perpusnas.go.id/files/media/" . $value;
    }
}
