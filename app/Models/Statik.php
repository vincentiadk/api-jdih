<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class Statik extends Model
{
    protected $table = 'tbl_statik';
    protected $primaryKey = 'id_artikel';
    protected $appends = ['file_location'];
    
    public function kategori()
    {
        return $this->belongsTo('App\Models\Kategori', 'id_kategori');
    }

    public function getFileLocationAttribute($value)
    {
        return "https://api-jdih.perpusnas.go.id/galeri/file/" . $this->id_artikel;
    }
    /*public function getFileAttribute($value)
    {
        return "https://api-jdih.perpusnas.go.id/statik/file/" . $this->
        return "https://jdih.perpusnas.go.id/uploads/" . $value;
    }*/

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
