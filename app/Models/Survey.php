<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;


class Survey extends Model
{
    protected $table = 'tbl_vote';
    protected $primaryKey = 'idvote';
    protected $fillable = [
        'idsessionuser', 'pilihan', 'tanggal', 'nama','masukan','instansi','email','telephone'
    ];
    public $timestamps = false;
    
}
