<?php

/** @var \Laravel\Lumen\Routing\Router $router */

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It is a breeze. Simply tell Lumen the URIs it should respond to
| and give it the Closure to call when that URI is requested.
|
*/

$router->get('/', function () use ($router) {
    return redirect('/docs/index.html');//$router->app->version();
});

$router->get('/token', 'TokenController@getToken');
$router->get('/peraturan/file/{id_peraturan}', 'PeraturanController@getFile');
$router->get('/galeri/file/{id}', 'GaleriController@getFile');
$router->get('/berita/file/{id}', 'BeritaController@getFile');
$router->get('/rancangan/file/{id}', 'RancanganPeraturanController@getFile');
$router->get('/gambar/file/{id}', 'GaleriController@getGambar');
$router->get('/thumb/file/{id}', 'GaleriController@getThumb');
$router->get('/CatalogTag', 'CatalogController@getCatalog');
$router->post('/authority/save/single', 'CatalogController@saveAuthoritySingle');
$router->post('/authority/save/multiple', 'CatalogController@saveAuthoritySingleMultiple');
$router->group(['middleware'=> 'auth'], function($router) {
    $router->get('/list-peraturan', 'PeraturanController@getListPeraturan');
    $router->get('/peraturan/{id_peraturan}/detail', 'PeraturanController@getDetailPeraturan');
    

    $router->get('/list-rancangan', 'RancanganPeraturanController@getListRancangan');
    $router->get('/rancangan/{id}/detail', 'RancanganPeraturanController@getDetailRancangan');
    $router->post('/masukan/{id}/save', 'MasukanPeraturanController@save');

    $router->get('/list-kategori', 'PeraturanController@getListKategori');

    $router->get('/list-monograf', 'MonografController@getListMonograf');
    $router->get('/monograf/{id_monograf}/detail', 'MonografController@getDetailMonograf');
    //$router->get('/monograf/download-ipusnas', 'MonografController@donwloadBookIPusnas');

    $router->get('/list-artikel', 'ArtikelController@getListArtikel');
    $router->get('/artikel/{id_artikel}/detail', 'ArtikelController@getDetailArtikel');

    $router->get('/list-berita', 'BeritaController@getListBerita');
    $router->get('/berita/{id}/detail', 'BeritaController@getDetailBerita');

    $router->get('/list-galeri', 'GaleriController@getListGaleri');
    $router->get('/galeri/{id}/detail', 'GaleriController@getDetailGaleri');

    $router->get('/profil/visi-misi', 'ProfilController@getVisiMisi');
    $router->get('/profil/tentang', 'ProfilController@getTentang');
    $router->get('/profil/struktur', 'ProfilController@getStruktur');

    $router->post('/survey/save', 'SurveyController@save');
    $router->get('/survey/hasil', 'SurveyController@getHasilSurvey');
    $router->get('/search/count', 'SearchController@getCount');
});