<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use App\Models\Artikel;


class ArtikelController extends Controller
{
    public function getListArtikel(Request $request)
    {
        $return = [];
        if(request('page')){
            $page = request('page') - 1;
        } else {
            $page = 0;
        }
        if(request('limit')){
            $limit = request('limit');
        } else {
            $limit = 10;
        }
        $q = Artikel::select(
            'id_artikel',
            'judul', 
            'deskripsi',
            'keywords', 
            'tanggal', 
            'view',
            'file'
            )->where('id_kategori', 694)
            ->where('display', 1)
            ->orderBy('tanggal', 'desc');
        $req_all = $request->all();
        foreach($req_all as $key=>$val){
            if($key != 'limit' && $key != 'page') {
                $q->where($key, 'LIKE', '%' . $val . '%');
            }
        }
        $return["total"] = $q->count();
        $return["page"] = $page + 1;
        $return["limit"] = $limit;
        $return["data"] =  $q->skip($page * $limit)->take($limit)->get();
        return $return;
    }

    public function getDetailArtikel($id)
    {
        $p = Artikel::findorFail($id);
        $jml_view = intval($p->view) + 1;
        if($p){
            $p->update([
                'view' => $view
            ]);
        }
        return $p;
    }

}
