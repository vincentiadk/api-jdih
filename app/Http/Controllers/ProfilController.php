<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use App\Models\Statik;

/**
  * @group Profil
*/
class ProfilController extends Controller
{
    public function getVisiMisi(Request $request)
    {
        return $this->getStatik(54);
    }
    public function getStruktur(Request $request)
    {
        return $this->getStatik(78);
    }
    public function getTentang(Request $request)
    {
        return $this->getStatik(93);
    }
    public function getStatik($id)
    {
        $p = Statik::find($id);
        return [
            'judul' => $p->judul,
            'deskripsi' => $p->deskripsi,
            'file' => "https://jdih.perpusnas.go.id/uploads/" . $p->file_statik
        ];
    }

}
