<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;


class Monograf extends Model
{
    protected $table = 'tbl_monograf';
    protected $primaryKey = 'id_monograf';
    protected $fillable = [
        'id_ori',
        'app_name',
        'title',
        'authors',
        'cover', 
        'isbn', 
        'eisbn',
        'extension', 
        'price', 
        'published_date', 
        'publisher', 
        'publication_place', 
        'qty', 
        'size', 
        'authors',
        'file',
    ];

    
}
