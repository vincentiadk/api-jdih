<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use App\Models\Peraturan;
use App\Models\Kategori;

class PeraturanController extends Controller
{
    public function getListPeraturan(Request $request)
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
        $q = Peraturan::select(
            'id_peraturan',
            'singkatan_jenis', 
            'judul',
            'nama_daerah', 
            'nomor_peraturan', 
            'tahun_peraturan', 
            'jml_donload as jml_download', 
            'jml_view',
            'file_peraturan',
            'tanggal_penetapan',
            'id_kategori'
            )->with('kategori')
            ->where('display', 1);
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

    public function getDetailPeraturan($id_peraturan)
    {
        $p = Peraturan::with(['kategori', 'lampiran'])->findorFail($id_peraturan);
        $jml_view = intval($p->jml_view) + 1;
        if($p){
            $p->update([
                'jml_view' => $jml_view
            ]);
        }
        return $p;
    }

    public function getListKategori()
    {
        $query = Peraturan::select("tbl_kategori.id_kategori", 'nama_kategori', \DB::raw('count(nama_kategori) as total'))
            ->join('tbl_kategori', 'tbl_kategori.id_kategori', 'tbl_peraturan.id_kategori')
            ->where('tbl_peraturan.display',1)
            ->groupBy('tbl_kategori.nama_kategori', 'tbl_kategori.id_kategori')
            ->get();
        return $query;
    }

}
