<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use GuzzleHttp\Client as GuzzleClient;
use App\Models\Berita;
use App\Models\Peraturan;
use App\Models\Statik;
use App\Models\TblAPI;

class SearchController extends Controller
{
    public function __construct()
    {
        $this->client = new GuzzleClient([
            'connect_timeout' => 86400.0,
            'timeout' => 86400.0,
            'verify' => false,
            'cookies' => true
        ]);
    
        $this->solr_url = env('SOLR_URL');
        $this->query = "/select?q=system_name:pkw AND table_name:records AND (archiveTitle:law OR archiveTitle:hukum) AND is_asset:1 ";
    }
    public function getCount(Request $request)
    {
        $return = [];
        $q = $request->get('q');
        $cBerita = Berita::where('id_kategori', 694)
            ->where('display', 1)
            ->where('judul', 'like', '%' . $q . '%')
            ->orWhere('deskripsi', 'like', '%' . $q . '%')
            ->count();

        $cPeraturan = Peraturan::where('display', 1)
            ->where('judul', 'like', '%' . $q . '%')
            ->orWhere('subjek','like', '%' . $q . '%')
            ->count();
        $cGambar = Statik::where('status',1)
                    ->where('judul', 'like', '%' . $q . '%')
                    ->where('id_kategori', 701) //gambar
                    ->count();
        $cVideo= Statik::where('status',1)
                    ->where('judul', 'like', '%' . $q . '%')
                    ->where('id_kategori', 702) //video
                    ->count();
        $cMonograf = TblAPI::where('system_name', 'ipusnas')
                    ->where('details', 'like', '%' . $q . '%')
                    ->count();
        $query = $this->query;
        $query .= ' AND (title:"' . strtolower($q) .'" OR description:"'. strtolower($q).'")';
        $query .= "&rows=0";
        $response = $this->client->get($this->solr_url. $query);
        $content = $response->getBody()->getContents();
        $content = json_decode($content, true)["response"];
        $cArtikel= $content["numFound"];
        return response()->json([
            'berita' => $cBerita,
            'peraturan' => $cPeraturan,
            'monograf' => $cMonograf,
            'artikel' => $cArtikel,
            'gambar' => $cGambar,
            'video' => $cVideo
        ]);
    }

}
