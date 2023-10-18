<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class Statik extends Model
{
    protected $table = 'tbl_statik';
    protected $primaryKey = 'id_artikel';
    
    public function kategori()
    {
        return $this->belongsTo('App\Models\Kategori', 'id_kategori');
    }

    public function getFileAttribute($value)
    {
        return "https://jdih.perpusnas.go.id/uploads/" . $value;
    }

    public function gambar()
    {
        return $this->hasMany('App\Models\Gambar', 'id_konten');
    }

    public function getTagAttribute($value)
    {
        if(trim($value) != "") {
            return "https://www.youtube.com/embed/" . $value;
        } else {
            return "";
        }
    }
}
