<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use App\Models\Statik;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\File;
use App\Models\Gambar;
/**
  * @group Galeri
*/
class GaleriController extends Controller
{
    /**
     * @header Authorization Bearer 123ABC-demoonly
     * @queryParam page integer Jika dikosongkan, maka default akan menampilkan halaman 1. Example: 1
     * @queryParam limit integer Jumlah data yang akan ditampilkan dalam 1 halaman.  Example: 3
     * @queryParam q string Pencarian berdasarkan query yang diinput oleh user. Example: rapat
     * @queryParam sort Melakukan sort/pengurutan data ascending (asc) atau descending (desc) berdasarkan field yang diinginkan. Field yang dapat dipakai "judul", "tanggal". Example: tanggal,desc
     * @queryParam type Bisa diisi dengan "gambar" atau "video" Example: gambar
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
                $query->select('id_konten','id_gambar', 'imagethumb', 'image');
            })
            ->whereIn('id_kategori', $type)
            ->where('status', 1);
        $req_all = $request->all();
        foreach($req_all as $key=>$val){
            if($key != 'limit' && $key != 'page' && $key != 'type' && $key != 'q' && $key != 'sort') {
                $q->where($key, 'LIKE', '%' . $val . '%');
            }
            if($key == 'q'){
                $q->where('judul', 'LIKE', '%' . $val . '%');
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
     * @urlParam id varchar required ID dari galeri yang akan dilihat detailnya. Example: 126
     */
    public function getDetailGaleri($id)
    {
        $p = Statik::select('*', \DB::raw('CASE WHEN id_kategori = 701 THEN "gambar" WHEN id_kategori = 702 THEN "video" END as type'))
        ->with('gambar', function ($query) {
            $query->select('id_konten','id_gambar', 'image1 as image', 'imagethumb');
        })->findorFail($id);
        return $p;
    }

    public function getFile($id)
    {
        $q = Statik::find($id);
        if($q) {
            $path = config('storage.statik') . $q->file_statik;
            if(File::exists($path) && !is_dir($path)) {
                return response()->download($path);
            } else {
                return "File tidak ditemukan";
            }
        } 
        return "ID galeri tidak ditemukan";
    }

    public function getGambar($id)
    {
        $q = Gambar::find($id);
        if($q) {
            $path = config('storage.galery') . $q->image1;
            if(File::exists($path) && !is_dir($path)) {
                return response()->download($path);
            } else {
                return "Gambar tidak ditemukan";
            }
        } 
        return "ID gambar tidak ditemukan";
    }

    public function getThumb($id)
    {
        $q = Gambar::find($id);
        if($q) {
            $path = config('storage.galery') . $q->getRawOriginal('imagethumb');
            if(File::exists($path) && !is_dir($path)) {
                return response()->download($path);
            } else {
                return "Thumb tidak ditemukan";
            }
        } 
        return "ID Thumb tidak ditemukan";
    }

}
