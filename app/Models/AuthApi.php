<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;


class AuthApi extends Model
{
    protected $table = 'tbl_auth_api';
    protected $primaryKey = 'id';
    protected $fillable = [
        'jwt', 'app_key', 'expired_at', 'enable',
    ];
    public $timestamps = true;
    
}
