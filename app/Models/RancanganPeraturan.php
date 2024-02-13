<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\MasukanPeraturan;

class RancanganPeraturan extends Model
{
    protected $table = 'tbl_rancangan_peraturan';
    protected $primaryKey = 'id';
    protected $fillable = [
        'file', 'status', 'judul', 'created_by','url', 'jml_view', 'jml_donload'
    ];
    public $timestamps = false;
    protected $appends = ['jml_masukan'];

    public function masukan()
    {
        return $this->hasMany('App\Models\MasukanPeraturan', 'id_rancangan_peraturan');
    }

    public function getFileAttribute($value)
    {
        return "https://api-jdih.perpusnas.go.id/rancangan/file/" . $this->id;
    }

    public function getJmlMasukanAttribute()
    {
        return MasukanPeraturan::where('id_rancangan_peraturan', $this->id)->count();
    }
}

