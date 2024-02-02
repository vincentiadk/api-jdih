<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use App\Models\Peraturan;
use App\Models\Kategori;
/**
  * @group Peraturan Perundangan
*/
class PeraturanController extends Controller
{
    /**
     * @queryParam page integer Jika dikosongkan, maka default akan menampilkan halaman 1. Example: 1
     * @queryParam limit integer Jumlah data yang akan ditampilkan dalam 1 halaman.  Example: 5
     * @queryParam q string Pencarian berdasarkan query yang diinput oleh user. Example: karya cetak
     * @queryParam sort Melakukan sort/pengurutan data ascending (asc) atau descending (desc) berdasarkan field yang diinginkan. Field yang dapat dipakai "judul", "deskripsi", "tanggal", "keywords". Example: tanggal,desc
     * @queryParam tahun_peraturan integer Pencarian berdasarkan tahun peraturan Example: 2018
     * @queryParam nomor_peraturan integer Pencarian berdasarkan nomor peraturan Example: 13
     * @queryParam singkatan_jenis Pencarian berdasarkan singkatan jenis peraturan Example: UU
     * @queryParam bahasa Jika diisi, akan mencari berdasarkan bahasa yang digunakan. Pilihan bahasa: "indonesia", "english" Example: indonesia
     * @queryParam id_kategori Pencarian berdasarkan id_kategori, gunakan tanda koma "," untuk pencarian banyak kategori sekaligus. Example: 3,751
     */
    public function getListPeraturan(Request $request)
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
            'tanggal_upload',
            'tanggal_perundangan',
            'id_kategori'
            )->with('kategori')
            ->where('display', 1);
        $req_all = $request->all();
        foreach($req_all as $key=>$val){
            if($key != 'limit' && $key != 'page' && $key != 'q' && $key != 'sort') {
                if($key == 'id_kategori'){
                    $k = explode(',', $val);
                    $q->whereIn('id_kategori', $k);
                } else {
                    $q->where($key, 'LIKE', '%' . $val . '%');
                }
            }
            if($key == 'q'){
                $q->where('judul', 'LIKE', '%' . $val . '%');
                
                $q->orWhere('subjek', 'LIKE', '%' . $val . '%');
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
     * @urlParam id_peraturan varchar required ID dari peraturan yang akan dilihat detailnya. Example: 414
     */
    public function getDetailPeraturan($id_peraturan)
    {
        $p = Peraturan::with(['kategori', 'status', 'lampiran'])
            ->findorFail($id_peraturan);
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
        $query = Peraturan::select("tbl_kategori.id_kategori", 'nama_kategori', \DB::raw('count(nama_kategori) as total'),'urutan')
            ->join('tbl_kategori', 'tbl_kategori.id_kategori', 'tbl_peraturan.id_kategori')
            ->where('tbl_peraturan.display',1)
            ->whereNotNull('urutan')
            ->groupBy('tbl_kategori.nama_kategori', 'tbl_kategori.id_kategori', 'urutan')
            ->orderBy('urutan', 'asc')
            ->get();
        return $query;
    }

}
