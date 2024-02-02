<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use App\Models\Berita;

/**
  * @group Berita
*/


class BeritaController extends Controller
{
    /**
     * @header Authorization Bearer 123ABC-demoonly
     * @queryParam page integer Jika dikosongkan, maka default akan menampilkan halaman 1. Example: 1
     * @queryParam limit integer Jumlah data yang akan ditampilkan dalam 1 halaman.  Example: 5
     * @queryParam q string Pencarian data berdasarkan query yang diinput oleh user. Example: karya cetak
     * @queryParam sort Melakukan sort/pengurutan data ascending (asc) atau descending (desc) berdasarkan field yang diinginkan. 
     * Field yang dapat dipakai "judul", "deskripsi", "tanggal", "keywords". Example: tanggal,desc
     */
    public function getListBerita(Request $request)
    {
        $return = [];
        if($request->input('page')){
            $page = $request->input('page') - 1;
        } else {
            $page = 0;
        }
        if($request->input('limit')){
            $limit = $request->input('limit');
        } else {
            $limit = 10;
        }
        $q = Berita::select(
            'id_artikel as id_berita',
            'judul', 
            'deskripsi',
            'keywords', 
            'tanggal', 
            'view',
            'file'
            )->where('id_kategori', 694)
            ->where('display', 1);
        $req_all = $request->all();
        foreach($req_all as $key=>$val){
            if($key != 'limit' && $key != 'page' && $key != 'q' && $key != 'sort') {
                $q->where($key, 'LIKE', '%' . $val . '%');
            }
            if($key == 'q'){
                $q->where('judul', 'LIKE', '%' . $val . '%');
                $q->orWhere('deskripsi', 'LIKE', '%' . $val . '%');
            }
            if($key == 'sort'){
                $sort = explode(',', $val);
                $q->orderBy($sort[0], $sort[1]);
            }
        }
        $return["total"] = $q->count();
        $return["page"] = $page + 1;
        $return["limit"] = $limit;
        $return["data"] =  $q->skip($page * $limit)->take($limit)->get();
        return $return;
    }
    /**
     * @header Authorization Bearer 123ABC-demoonly
     * @urlParam id integer required ID dari berita yang akan dilihat detailnya. Example: 77
     */
    public function getDetailBerita($id)
    {
        $p = Berita::select(
            'id_artikel as id_berita',
            'judul', 
            'deskripsi',
            'keywords', 
            'tanggal', 
            'view',
            'file')
            ->findorFail($id);
        $jml_view = intval($p->view) + 1;
        if($p){
            $p->update([
                'view' => $jml_view
            ]);
        }
        return $p;
    }

}
