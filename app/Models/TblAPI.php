<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;


class TblAPI extends Model
{
    protected $table = 'tbl_api';
    protected $primaryKey = 'record_id';
    protected $fillable = [
        'record_id', 'jml_view', 'date', "system_name", "jml_download", "details"
    ];
    public $timestamps = false;
    
}
