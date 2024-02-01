<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;


class Kategori extends Model
{
    protected $table = 'tbl_kategori';
    protected $primaryKey = 'id_kategori';
    
    public function peraturans()
    {
        return $this->HasMany('App\Models\Peraturan', 'id_kategori');
    }
    
}
