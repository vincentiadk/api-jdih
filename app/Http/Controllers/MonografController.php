<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use GuzzleHttp\Client as GuzzleClient;
use App\Models\TblAPI;
use Carbon\Carbon;
use DateTime;
/**
  * @group Monograf Hukum
*/
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
    /**
     * @queryParam page integer Jika dikosongkan, maka default akan menampilkan halaman 1. Example: 1
     * @queryParam limit integer Jumlah data yang akan ditampilkan dalam 1 halaman.  Example: 5
     * @queryParam q string Pencarian berdasarkan query yang diinput oleh user. Example: karya cetak
     * @queryParam sort Melakukan sort/pengurutan data ascending (asc) atau descending (desc) berdasarkan field yang diinginkan. Field yang dapat dipakai "date" Example: date,desc
     * @response {
    * "total": 1230,
    * "page": 1,
    * "limit": 10,
    * "data": [
    *     {
    *         "record_id": 82564,
    *         "jml_view": 0,
    *         "date": "2017-09-25 12:09:00",
    *         "system_name": "ipusnas",
    *         "jml_download": 0,
    *         "details": {
    *             "authors": "Alumni Fakultas Hukum Universitas Indonesia (FHUI) 1992",
    *             "categories_id": 281,
    *             "category": "Hukum",
    *             "cover": "https://static.ijakarta.id/publication/book/cover/6ae11180b4f5e612f20517b8760a460d.png",
    *             "description": "<p>Kuliah hukum? Mau jadi hakim ya? Atau pengacara atau jaksa? Notaris itu kerjanya apa sih? Menyiapkan kontrak? Apa bedanya dengan profesi pengacara? Untuk para mahasiswa fakultas hukum atau sarjana hukum yang baru mau berkarir di bidang hukum pasti sudah amat familiar dengan tuduhan mau menjadi hakim atau pengacara, atau kebingungan banyak orang untuk membedakan profesi notaris atau pengacara.<br />\r\n<br />\r\nLewat PROFESI HUKUM ITU ASYIK! Sarjana Hukum Bukan Sekadar Pengacara &amp; Hakim, kami para alumni Fakultas Hukum Universitas Indonesia (FHUI) angkatan 1992 akan mencoba mengenalkan beragam profesi hukum yang kelak dapat dijalani oleh adik-adik pelajar yang masih duduk di bangku Sekolah Menengah ataupun para mahasiswa fakultas hukum yang masih &lsquo;galau&rsquo; dalam menentukan karir di masa depan.<br />\r\n<br />\r\nTernyata seorang Sarjana Hukum (S.H.) dapat berkarier di berbagai bidang. Dalam buku ini kami coba pilihkan 25 profesi hukum yang dapat dijadikan acuan untuk berkarier, misalnya di bidang perbankan, migas, dan KPK, menjadi diplomat, wartawan, relawan, head hunter, serta masih banyak lainnya. Buku ini mencoba memperluas wawasan dan memberikan perspektif baru tentang profesi dalam bidang hukum yang dapat ditekuni.</p>\r\n",
    *              "eisbn": "978-602-06-1716-9",
    *             "eissn": "xxx-xxx-xx-xxxx-x",
    *             "extension": "pdf",
    *             "hash_id": "7453942e62180565ae59985d7cab2fdb",
    *             "id": 82564,
    *             "isbn": "978-602-03-7191-7",
    *             "num_pages": 0,
    *             "price": 75000,
    *             "published_date": "25-09-2017 00:00:00",
    *             "publisher": "PT. Gramedia Pustaka Utama",
    *             "publisher_raw": "pt. gramedia pustaka utama",
    *             "publishers_about": "None",
    *             "publishers_city": "kota jakarta barat; dki jakarta",
    *             "publishers_fax": "None",
    *             "publishers_id": 10,
    *            "publishers_logo": "https://static.ijakarta.id/img/libraries/logo/99ded1d9b86580756d1fac40f53ef2b2.jpeg",
    *             "publishers_status": 1,
    *             "qty": 20,
    *             "size": "3.8 MB",
    *             "status": 1,
    *             "title": "Profesi Hukum Itu Asyik! Sarjana Hukum: Bukan Sekadar Pengacara & Hakim"
    *         },
    *         {  
    *           .... 
    *         }
    *     },

    */
    public function getListMonograf(Request $request)
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
    /**
     * @urlParam id_monograf integer required ID dari monograf yang akan dilihat detailnya. Example: 82564
     */
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
