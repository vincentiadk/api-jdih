<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;


class Peraturan extends Model
{
    protected $table = 'tbl_peraturan';
    protected $primaryKey = 'id_peraturan';
    protected $fillable = [
        'file_peraturan', 'nomor_peraturan', 'judul', 'tempat_terbit'
    ];
    public function kategori()
    {
        return $this->belongsTo('App\Models\Kategori', 'id_kategori');
    }

    public function getFilePeraturanAttribute($value)
    {
        return "https://jdih.perpusnas.go.id/file_peraturan/" . $value;
    }
}
