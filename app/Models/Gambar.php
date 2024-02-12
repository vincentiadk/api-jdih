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
        if(file_get_contents(config('storage.galery') . $value)) {
            return "data:image/png;base64," . base64_encode(file_get_contents(config('storage.galery') . $value));
        } else {
            return "";
        }
    }

    public function getImagethumbAttribute($value)
    {
        if(file_get_contents(config('storage.galery') . $value)) {
            return "data:image/png;base64," . base64_encode(file_get_contents(config('storage.galery') . $value));
        } else {
            return "";
        }
    }
}
