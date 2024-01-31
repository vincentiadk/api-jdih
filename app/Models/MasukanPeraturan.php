<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;


class MasukanPeraturan extends Model
{
    protected $table = 'tbl_masukan_rancangan';
    protected $primaryKey = 'id';
    
    public function rancanganperaturan()
    {
        return $this->belongsTo('App\Models\RancanganPeraturan', 'id_rancangan_peraturan');
    }
}
