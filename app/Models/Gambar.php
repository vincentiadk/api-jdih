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
        /*if(File::exists(config('storage.galery') . $value) && !is_dir(config('storage.galery') . $value)) {
            return "data:image/png;base64," . base64_encode(file_get_contents(config('storage.galery') . $value));
        } else {
            return "";
        }*/
    }

    public function getImagethumbAttribute($value)
    {
        if(File::exists(config('storage.galery') . $value) && !is_dir(config('storage.galery') . $value)) {
            return "data:image/png;base64," . base64_encode(file_get_contents(config('storage.galery') . $value));
        } else {
            return "";
        }
    }
}
