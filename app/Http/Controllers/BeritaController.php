<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use App\Models\Berita;


class BeritaController extends Controller
{
    public function getListBerita(Request $request)
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
        $q = Berita::select(
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
            if($key != 'limit' && $key != 'page' && $key != 'q') {
                $q->where($key, 'LIKE', '%' . $val . '%');
            }
            if($key == 'q'){
                $q->where('judul', 'LIKE', '%' . $val . '%');
                $q->orWhere('deskripsi', 'LIKE', '%' . $val . '%');
            }
        }
        $return["total"] = $q->count();
        $return["page"] = $page + 1;
        $return["limit"] = $limit;
        $return["data"] =  $q->skip($page * $limit)->take($limit)->get();
        return $return;
    }

    public function getDetailBerita($id)
    {
        $p = Berita::findorFail($id);
        $jml_view = intval($p->view) + 1;
        if($p){
            $p->update([
                'view' => $jml_view
            ]);
        }
        return $p;
    }

}
