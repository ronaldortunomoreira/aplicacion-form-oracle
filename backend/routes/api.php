<?php

use App\Http\Controllers\Api\AccommodationController;
use App\Http\Controllers\Api\AccommodationTypeController;
use App\Http\Controllers\Api\DestinationController;
use App\Http\Controllers\Api\EmpresaController;
use App\Http\Controllers\Api\PaqueteTuristicoController;
use App\Http\Controllers\Api\ReservationController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// Categories / Types
Route::get('/accommodation-types', [AccommodationTypeController::class, 'index']);

// Accommodations & destinations
//
// `/cities` alimenta el autocomplete del buscador con las ciudades donde hay
// alojamientos. `/destinations` es el catálogo editorial de destinos turísticos,
// una entidad propia con su página. Son cosas distintas y por eso tienen rutas
// distintas: antes el autocomplete vivía en `/destinations` y el nombre choca.
Route::get('/cities', [AccommodationController::class, 'cities']);
Route::get('/destinations', [DestinationController::class, 'index']);
Route::get('/destinations/{slug}', [DestinationController::class, 'show']);
Route::get('/travel-packages', [PaqueteTuristicoController::class, 'index']);
Route::get('/travel-packages/{slug}', [PaqueteTuristicoController::class, 'show']);
Route::get('/countries', [AccommodationController::class, 'countries']);
Route::get('/countries/{slug}', [AccommodationController::class, 'country']);
Route::get('/accommodations', [AccommodationController::class, 'index']);

// By slug before by id, so the descriptive URL never collides with the numeric one.
Route::get('/accommodations/slug/{slug}', [AccommodationController::class, 'showBySlug']);
Route::get('/accommodations/{id}', [AccommodationController::class, 'show'])->whereNumber('id');

// Reservations
Route::post('/reservations/preview', [ReservationController::class, 'preview']);
Route::post('/reservations', [ReservationController::class, 'store']);
Route::post('/reservations/package', [ReservationController::class, 'storePackage']);
Route::post('/reservations/lookup', [ReservationController::class, 'lookup']);
Route::put('/reservations/{code}/cancel', [ReservationController::class, 'cancel']);

// Company requests: public on both ends, since the page shows them as they arrive.
// /company-countries is the catalogue for the registration form alone; it is
// intentionally not the same as /countries, which lists only countries that
// have published accommodations.
Route::get('/company-countries', [EmpresaController::class, 'paises']);
Route::get('/companies', [EmpresaController::class, 'index']);
Route::post('/companies', [EmpresaController::class, 'store']);

// Sanctum user endpoint (optional)
Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');
