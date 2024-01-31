<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;


class RancanganPeraturan extends Model
{
    protected $table = 'tbl_rancangan_peraturan';
    protected $primaryKey = 'id';
    protected $fillable = [
        'file', 'status', 'judul', 'created_by','url', 'jml_view', 'jml_donload'
    ];
    public $timestamps = false;

    public function masukan()
    {
        return $this->hasMany('App\Models\MasukanPeraturan', 'id_rancangan_peraturan');
    }


}
