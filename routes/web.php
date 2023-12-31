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
    return $router->app->version();
});
$router->get('/list-peraturan', 'PeraturanController@getListPeraturan');
$router->get('/peraturan/{id_peraturan}/detail', 'PeraturanController@getDetailPeraturan');
$router->get('/list-kategori', 'PeraturanController@getListKategori');

$router->get('/list-monograf', 'MonografController@getListMonograf');
$router->get('/monograf/{id_monograf}/detail', 'MonografController@getDetailMonograf');

$router->get('/list-artikel', 'ArtikelController@getListArtikel');
$router->get('/artikel/{id}/detail', 'ArtikelController@getDetailArtikel');

$router->get('/list-galeri', 'GaleriController@getListGaleri');
$router->get('/galeri/{id}/detail', 'GaleriController@getDetailGaleri');

$router->get('/profil/visi-misi', 'ProfilController@getVisiMisi');
$router->get('/profil/tentang', 'ProfilController@getTentang');
$router->get('/profil/struktur', 'ProfilController@getStruktur');

$router->post('/survey/save', 'SurveyController@saveSurvey');
$router->get('/survey/hasil', 'SurveyController@getHasilSurvey');

$router->get('/search/count', 'SearchController@getCount');
