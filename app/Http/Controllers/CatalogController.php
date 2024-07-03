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
        $data = DB::connection('inlis')
            ->table('CATALOG_RUAS_FIX')
            ->select('Catalogid', 'Tag', 'Indicator1', 'Indicator2', 'Value')
            ->where('catalogid', $request->input('ID'))
            ->get();
        return response()->json(
            [
                "Data" => $data,
            ]
        );
    }
}
