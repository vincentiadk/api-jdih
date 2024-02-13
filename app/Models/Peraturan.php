<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;


class Peraturan extends Model
{
    protected $table = 'tbl_peraturan';
    protected $primaryKey = 'id_peraturan';
    protected $fillable = [
        'file_peraturan', 'nomor_peraturan', 'judul', 'tempat_terbit','jml_view'
    ];
    public $timestamps = false;
    public function kategori()
    {
        return $this->belongsTo('App\Models\Kategori', 'id_kategori');
    }

    public function getFilePeraturanAttribute($value)
    {
        return "https://api-jdih.perpusnas.go.id/peraturan/file/" . $this->id_peraturan;
    }

    public function lampiran()
    {
        return $this->HasMany('App\Models\Lampiran', 'id_peraturan')->where('keterangan', '!=', "");
    }

    public function status()
    {
        return $this->HasMany('App\Models\Status', 'id_peraturan');
    }

    public function getTempatTerbitAttribute($value)
    {
        if(trim($value) == "") {
            return "Jakarta";
        } else {
            return $value;
        }
    }

    public function getCetakanAttribute($value)
    {
        if(trim($value) == "") {
            return "Pertama";
        } else {
            return $value;
        }
    }
    public function getBahasaAttribute($value)
    {
        if(trim($value) == "") {
            return "Bahasa Indonesia";
        } else {
            return $value;
        }
    }
    public function getBidangHukumAttribute($value)
    {
        if(trim($value) == "") {
            return "Hukum Administrasi Negara";
        } else {
            return $value;
        }
    }
    public function getLokasiBukuAttribute($value)
    {
        if(trim($value) == "") {
            return "Biro Hukum, Organisasi, Kerjasama dan Hubungan Masyarakat Perpustakaan Nasional";
        } else {
            return $value;
        }
    }
    public function getPengarangAttribute($value)
    {
        if(trim($value) == "") {
            return "Indonesia.";
        } else {
            return $value;
        }
    }

    public function getNamaLembagaAttribute($value)
    {
        if(trim($value) == "" && strpos( strtolower($this->judul), "perpustakaan nasional" ) !== false) {
            return "Perpustakaan Nasional RI";
        } else {
            return $value;
        }
    }

}
