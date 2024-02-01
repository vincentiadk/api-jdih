<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;


class Status extends Model
{
    protected $table = 'tbl_status';
    protected $primaryKey = 'id_status';
    protected $fillable = [
        'id_peraturan', 'tanggal', 'status', 'keterangan','id_parent_peraturan'
    ];
    public $timestamps = false;

    public function peraturan()
    {
        return $this->belongsTo('App\Models\Oeraturan', 'id_peraturan');
    }

}
