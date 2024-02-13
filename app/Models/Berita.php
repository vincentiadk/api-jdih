<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;


class Berita extends Model
{
    protected $table = 'tbl_artikel';
    protected $primaryKey = 'id_artikel';
    protected $fillable = [
        'view'
    ];
    public $timestamps = false;
    
    public function kategori()
    {
        return $this->belongsTo('App\Models\Kategori', 'id_kategori');
    }

    public function getFileAttribute($value)
    {
        return "https://api-jdih.perpusnas.go.id/berita/file/" . $this->id_berita; //upload
    }
}
