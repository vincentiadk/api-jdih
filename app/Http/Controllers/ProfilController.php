<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use App\Models\Statik;


class ProfilController extends Controller
{
    public function getVisiMisi(Request $request)
    {
        $p = Statik::find(54); 
        return [
            'judul' => $p->judul,
            'deskripsi' => $p->deskripsi,
            'file' => "https://jdih.perpusnas.go.id/uploads/" . $p->file_statik
        ];
    }
    public function getStruktur(Request $request)
    {
        $p = Statik::find(78); 
        return [
            'judul' => $p->judul,
            'deskripsi' => $p->deskripsi,
            'file' => "https://jdih.perpusnas.go.id/uploads/" . $p->file_statik
        ];
       
    }

    public function getTentang(Request $request)
    {
        $p = Statik::find(93);
        return [
            'judul' => $p->judul,
            'deskripsi' => $p->deskripsi,
            'file' => "https://jdih.perpusnas.go.id/uploads/" . $p->file_statik
        ];
    }

}
