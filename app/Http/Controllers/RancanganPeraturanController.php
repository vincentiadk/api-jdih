<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use App\Models\RancanganPeraturan;
use App\Models\MasukanPeraturan;
/**
  * @group Peraturan Perundangan
*/
class RancanganPeraturanController extends Controller
{
    /**
     * @urlParam page integer Jika dikosongkan, maka default akan menampilkan halaman 1. Example: 1
     * @urlParam limit integer Jumlah data yang akan ditampilkan dalam 1 halaman.  Example: 5
     * @urlParam q string Pencarian berdasarkan query yang diinput oleh user. Example: karya cetak
     * @urlParam sort Melakukan sort/pengurutan data ascending (asc) atau descending (desc) berdasarkan field yang diinginkan. Field yang dapat dipakai "judul", "created". Example: created,desc
     * 
     */
    public function getListRancangan(Request $request)
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
        $q = RancanganPeraturan::select(
            'id',
            'judul',
            'file', 
            'jml_view', 
            'jml_donload', 
            'created',
            )->with('masukan')
            ->where('status', 1); //status 1 = selesai, status = 2 penyusunan
        $req_all = $request->all();
        foreach($req_all as $key=>$val){
            if($key != 'limit' && $key != 'page' && $key != 'q') {
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
    /**
     * @urlParam id integer required ID dari rancangan peraturan yang akan dilihat detailnya. Example: 4
     */
    public function getDetailRancangan($id)
    {
        $p = RancanganPeraturan::with(['masukan'])->findorFail($id);
        $jml_view = intval($p->jml_view) + 1;
        if($p){
            $p->update([
                'jml_view' => $jml_view
            ]);
        }
        return $p;
    }

}
