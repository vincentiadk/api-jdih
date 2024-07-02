<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use GuzzleHttp\Client as GuzzleClient;
use Carbon\Carbon;
use DB;
use Illuminate\Support\Facades\File;

/**
  * @group Artikel Hukum
*/
class CatalogController extends Controller
{

    public function getCatalog(Request $request)
    {
        $data = DB::table('INLIS.CATALOG_RUAS')
            ->select('catalogid', 'tag', 'indicator1', 'indicator2', 'value')
            ->where('catalogid', $request->input('ID'))
            ->get();
        return response()->json(
            [
                "data" => $data,
            ]
        );
    }
}
