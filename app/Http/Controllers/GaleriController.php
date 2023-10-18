<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use App\Models\Statik;
use Illuminate\Database\Eloquent\Builder;

class GaleriController extends Controller
{
    public function getListGaleri(Request $request)
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
        if(request('type')){
            $type = request('type');
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
            ->where('status', 1)
            ->orderBy('tanggal', 'desc');
        $req_all = $request->all();
        foreach($req_all as $key=>$val){
            if($key != 'limit' && $key != 'page' && $key != 'type') {
                $q->where($key, 'LIKE', '%' . $val . '%');
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
        $p = Statik::with('gambars')->findorFail($id);
        return $p;
    }

}
