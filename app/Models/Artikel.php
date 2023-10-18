<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;


class Artikel extends Model
{
    protected $table = 'tbl_artikel';
    protected $primaryKey = 'id_artikel';
    
    public function kategori()
    {
        return $this->belongsTo('App\Models\Kategori', 'id_kategori');
    }

    public function getFileAttribute($value)
    {
        return "https://jdih.perpusnas.go.id/uploads/" . $value;
    }
}
