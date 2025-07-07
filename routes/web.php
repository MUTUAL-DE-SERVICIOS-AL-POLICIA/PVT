<?php
use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
 */

 Route::get('/horizon-queues', function (Request $request) {
    $endpoint = trim($request->query('t'));
    if (!$endpoint) {
        abort(403, 'Acceso no autorizado');
    }
    return redirect('/' . md5($endpoint));
});

Route::get('{any}', function () {
    return view('index');
})->where('any', '^(?!.*(api|docs)).*$')->name('index.php');