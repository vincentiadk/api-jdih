<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use App\Models\Statik;
use Illuminate\Database\Eloquent\Builder;

/**
  * @group Galeri
*/
class GaleriController extends Controller
{
    /**
     * @urlParam page integer Jika dikosongkan, maka default akan menampilkan halaman 1. Example: 1
     * @urlParam limit integer Jumlah data yang akan ditampilkan dalam 1 halaman.  Example: 2
     * @urlParam q string Pencarian berdasarkan query yang diinput oleh user.
     * @urlParam sort Melakukan sort/pengurutan data ascending (asc) atau descending (desc) berdasarkan field yang diinginkan. Field yang dapat dipakai "judul", "tanggal". Example: tanggal,desc
     * @urlParam type Bisa diisi dengan "gambar" atau "video" Example: gambar
     * 
     */
    public function getListGaleri(Request $request)
    {
        $return = [];
        if($request->input('page')){
            $page = intval($request->input('page')) - 1;
        } else {
            $page = 0;
        }
        if($request->input('limit')){
            $limit = $request->input('limit');
        } else {
            $limit = 10;
        }
        if($request->input('type')){
            $type = $request->input('type');
            if($type == 'gambar'){
                $type = [701];
            } else {
                $type = [702];
            }
        } else {
            $type = [701, 702];
        }
        $q = Statik::select(
            'id_artikel',
            'judul',
            'tanggal',
            'tag',
            \DB::raw('CASE WHEN id_kategori = 701 THEN "gambar" WHEN id_kategori = 702 THEN "video" END as type')
            )->with('gambar', function ($query) {
                $query->select('id_konten','id_gambar', 'image1 as image', 'imagethumb');
            })
            ->whereIn('id_kategori', $type)
            ->where('status', 1);
        $req_all = $request->all();
        foreach($req_all as $key=>$val){
            if($key != 'limit' && $key != 'page' && $key != 'type' && $key != 'q') {
                $q->where($key, 'LIKE', '%' . $val . '%');
            }
            if($key == 'q'){
                $q->where('judul', 'LIKE', '%' . $val . '%');
            }
        }
        $return["total"] = $q->count();
        $return["page"] = $page + 1;
        $return["limit"] = $limit;
        $return["data"] =  $q->skip($page * $limit)->take($limit)->get();
        return $return;
    }

    public function getDetailGaleri($id)
    {
        $p = Statik::select('*', \DB::raw('CASE WHEN id_kategori = 701 THEN "gambar" WHEN id_kategori = 702 THEN "video" END as type'))
        ->with('gambar', function ($query) {
            $query->select('id_konten','id_gambar', 'image1 as image', 'imagethumb');
        })->findorFail($id);
        return $p;
    }

}
