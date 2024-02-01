<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use GuzzleHttp\Client as GuzzleClient;
use App\Models\TblAPI;
use Carbon\Carbon;
use DateTime;

class MonografController extends Controller
{
    public $client;
    public $solr_url;
    public $query;

    public function __construct()
    {
        $this->client = new GuzzleClient([
            'base_uri' => "https://search.aksaramaya.id/books",
            'http_errors' => false,
            'verify' => false,

        ]);
    }

    public function donwloadBookIPusnas()
    {
        $response = $this->client->get("?q=hukum&app=ipusnas");
        $meta = json_decode($response->getBody()->getContents(), true)["meta"];
        for($i = 1; $i< $meta["total_pages"]; $i++){
            $q = $this->client->get("?q=hukum&app=ipusnas&page=" . $i);
            $data = json_decode($q->getBody()->getContents(), true);
            if($data != null) {
                if(array_key_exists("data", $data)){
                    foreach($data["data"] as $d){
                        $arr = $d["Book"];
                        $date = new DateTime($arr["published_date"]);
                        $year = intval($date->format('Y'));
                        if($year < 1970) {
                            $date = Carbon::now();
                        }
                        TblApi::updateOrCreate([
                            'record_id' => $arr["id"],
                            'system_name' => 'ipusnas'
                        ],[
                            'record_id' => $arr["id"],
                            'details' => json_encode($arr),
                            'system_name' => 'ipusnas',
                            'jml_view' => 0,
                            'jml_download' => 0,
                            'date' => $date->format('Y-m-d h:m:s')
                        ]);
                    }
                }
            }
        }
        return "Download Success!";
    }

    public function getListMonograf(Request $request)
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
        $q = TblApi::select('record_id', 'jml_view', 'date', "system_name", "jml_download", "details")
            ->where('system_name', 'ipusnas');
        $req_all = $request->all();
        foreach($req_all as $key=>$val){
            if($key == 'q'){
                $q->where('details', 'LIKE', '%' . $val . '%');
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

    public function getDetailMonograf($id_monograf)
    {
        $p = TblApi::where('record_id', $id_monograf)->where('system_name', 'ipusnas')->first();
        $jml_view = intval($p->jml_view) + 1;
        if($p){
            $p->update([
                'jml_view' => $jml_view
            ]);
        }
        return $p;
    }
}
