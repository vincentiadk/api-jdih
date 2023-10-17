<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use GuzzleHttp\Client as GuzzleClient;

class MonografController extends Controller
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
        $this->query = "/select?q=system_name:pkw AND table_name:records AND (archiveTitle:law OR archiveTitle:hukum) ";
    }

    public function getListMonograf()
    {
        $page = request('page') && request('page') != "null" ? intval(request('page')) - 1 : 0;
        $limit = request('limit') && request('limit') != "null"? intval(request('limit'))  : 10;
        $query = $this->query;
        $q_vall = "";
        
        if("null" !== request('q')){
            $query .= ' AND (title:"' . strtolower(request('q')) .'" OR description:"'. strtolower(request('q')).'")';
            $q_vall .= "q=" . request('q');
        }
        if(null != request('id')){
            $query .= ' AND -id:' . request('id');
        }

        $query .= "&start=" . $page*$limit . "&rows=$limit";
        $response = $this->client->get($this->solr_url. $query);
        $content = $response->getBody()->getContents();
        $content = json_decode($content, true)["response"];
        $docs = $content["docs"];
        $numFound = $content["numFound"];
        $res = [];
        foreach($docs as $doc){
            $type = "";
            if(!isset($doc['type']) && (strpos(strtolower($doc['download_original']), "youtube") !== false)){
                $type = "video";
            } else if(strpos($doc['download_original'], "journal") !== false) {
                $type = "article";
            } else {
                $type = $doc['type'][0];
            }
            array_push($res,[
                'id' => $doc['id'],
                'title' => isset($doc['title'][0]) ? $doc['title'][0] : "",
                'creator' => isset($doc['creator_string']) ? $doc['creator_string'][0] : '',
                'description' => isset($doc['description']) ? $doc['description'][0] : '',
                'type' => $type,
                'subject' => isset($doc['subject']) ? $doc['subject'][0] : '',
                'link' => isset($doc['identifier2']) ? $doc['identifier2'][0] : '',
                'created_at' => isset($doc['date']) ? substr($doc['date'][0], 0, 10) : '',
            ]);
        }
        return response()->json(
            [
                "query" => $q_vall,
                "total" => $numFound,
                "page" => request('page') == 'null' ? 1 : request('page'),
                "limit" => $limit,
                "data" => $res,
            ]
        );
    }

}
