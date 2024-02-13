<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\File;

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
        return "https://api-jdih.perpusnas.go.id/gambar/file/" . $this->id_gambar;
    }

    public function getImagethumbAttribute($value)
    {
        return "https://api-jdih.perpusnas.go.id/thumb/file/" . $this->id_gambar;
    }
}
