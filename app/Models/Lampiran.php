<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;


class Lampiran extends Model
{
    protected $table = 'tbl_lampiran';
    protected $primaryKey = 'id_lampiran';
    
    public function peraturan()
    {
        return $this->belongsTo('App\Models\Peraturan', 'id_peraturan');
    }

    public function getKeteranganAttribute($value)
    {
        return "https://jdih.perpusnas.go.id/file_peraturan/" . $value;
    }
}
