<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use GuzzleHttp\Client as GuzzleClient;
use App\Models\TblAPI;
use Carbon\Carbon;

/**
  * @group Artikel Hukum
*/
class ArtikelController extends Controller
{
    public $client;
    public $solr_url;
    public $query;

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

    /**
     * @queryParam page integer Jika dikosongkan, maka default akan menampilkan halaman 1. Example: 2
     * @queryParam limit integer Jumlah data yang akan ditampilkan dalam 1 halaman.  Example: 3
     * @queryParam q string Pencarian data berdasarkan query yang diinput oleh user. Example: hukum islam
     * @queryParam sort Melakukan sort/pengurutan data ascending (asc) atau descending (desc) berdasarkan field yang diinginkan. 
     * Field yang dapat dipakai "title", "creator", "type", "subject", "created_at". Example: created_at,desc
     */
    public function getListArtikel(Request $request)
    {
        $page = null !== $request->input('page')  ? intval($request->input('page')) - 1 : 0;
        $limit = null !== $request->input('limit') ? intval($request->input('limit'))  : 10;
        $query = $this->query;
        $q_vall = "";
        
        if("null" !== $request->input('q')){
            $query .= ' AND (title:"' . strtolower($request->input('q')) .'" OR description:"'. strtolower($request->input('q')).'")';
            $q_vall .= "q=" . $request->input('q');
        }
        if(null != $request->input('id')){
            $query .= ' AND -id:' . $request->input('id');
        }

        $query .= "&start=" . $page*$limit . "&rows=$limit";

        if(null != $request->input('sort')){
            $sort = explode(',', $request->input('sort'));
            if($sort[0] == 'title' || $sort[0] == 'creator' || $sort[0] == 'subject' ){
                $query .= '&sort=field(' . $sort[0] . ') ' . $sort[1];
            } else {
                $query .= '&sort=' . $sort[0] . ' ' . $sort[1];
            }
        }
        $response = $this->client->get($this->solr_url. $query);
        $content = $response->getBody()->getContents();
        $content = json_decode($content, true)["response"];
        $docs = $content["docs"];
        $numFound = $content["numFound"];
        $res = [];
        foreach($docs as $doc){
            $type = "";
            if(!isset($doc['type']) && (strpos(strtolower(isset($doc['download_original'])), "youtube") !== false)){
                $type = "video";
            } else if(strpos(isset($doc['download_original']), "journal") !== false) {
                $type = "article";
            } else {
                $type = isset($doc['type']) ? $doc['type'][0] : "-";
            }
            $record_id = $doc['record_id'][0];
            array_push($res,[
                'id' => $doc['id'],
                'title' => isset($doc['title'][0]) ? $doc['title'][0] : "",
                'creator' => isset($doc['creator_string']) ? $doc['creator_string'][0] : '',
                'description' => isset($doc['description']) ? $doc['description'][0] : '',
                'type' => $type,
                'subject' => isset($doc['subject']) ? $doc['subject'][0] : '',
                //'file' => $this->getListFiles($record_id),
                //'link' => isset($doc['identifier2']) ? $doc['identifier2'][0] : '',
                'created_at' => isset($doc['date']) ? substr($doc['date'][0], 0, 10) : '',
            ]);
        }
        return response()->json(
            [
                "query" => $q_vall,
                "total" => $numFound,
                "page" => $request->input('page') ? $request->input('page') : 1,
                "limit" => $limit,
                "data" => $res,
            ]
        );
    }
    public function getListFiles($id)
    {
        $response = $this->client->get("https://interoperabilitas.perpusnas.go.id/list/files-api/" . $id);
        $content = $response->getBody()->getContents();
        $content = json_decode($content, true);
        return $content;
    }

    /**
     * @urlParam id_artikel varchar required ID dari artikel yang akan dilihat detailnya. Example: fdaea816-d08e-44e7-9323-0098c4b2f3fe
     */
    public function getDetailArtikel($id_artikel)
    {
        $query = $this->query;
        $query .= ' AND id:' . $id_artikel;

        $query .= "&rows=1";
        $response = $this->client->get($this->solr_url. $query);
        $content = $response->getBody()->getContents();
        $content = json_decode($content, true)["response"];
        if(isset($content["docs"][0])){
            $doc = $content["docs"][0];
        } else {
            return response()->json(["message"=> "ID tidak ditemukan"], 503);
        }
        $res = [];
       
        $type = "";
        if(!isset($doc['type']) && (strpos(strtolower(isset($doc['download_original'])), "youtube") !== false)){
            $type = "video";
        } else if(strpos(isset($doc['download_original']), "journal") !== false) {
            $type = "article";
        } else {
                $type = $doc['type'][0];
        }
        $record_id = $doc['record_id'][0];
        $p = TblAPI::find($record_id);
        $jml = 1;
        if($p){
            $jml = intval($p->jml_view) + 1;
            $p->update([
                'jml_view' => $jml,
                'system_name' => 'pkw',
            ]);
        } else {
            TblAPI::create([
                'record_id' => $record_id,
                'jml_view' => 1,
                'jml_download' => 0,
                'date' => Carbon::now(),
                'system_name' => "pkw"
            ]);
        }
        return response()->json([
            'id' => $doc['id'],
            'title' => isset($doc['title'][0]) ? $doc['title'][0] : "",
            'creator' => isset($doc['creator_string']) ? $doc['creator_string'][0] : '',
            'publisher' =>  isset($doc['publisher_string']) ? $doc['publisher_string'][0] : '',
            'description' => isset($doc['description']) ? $doc['description'][0] : '',
            'type' => $type,
            'subject' => isset($doc['subject']) ? $doc['subject'][0] : '',
            'year' => isset($doc['year_string']) ? $doc['year_string'] : '',
            'source' => isset($doc['source']) ? $doc['source'] : '',
            'jml_view' => $jml,
            'file' => $this->getListFiles($record_id),
            'created_at' => isset($doc['date']) ? substr($doc['date'][0], 0, 10) : '',
        ]);
    }
}
