<?php

namespace App\Providers;

use App\Models\Accommodation;
use App\Models\AccommodationType;
use App\Models\PaqueteTuristico;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // La tabla `imagenes` guarda el dueño de la foto con un texto corto y
        // legible en la columna `recurso_tipo` en lugar de la clase completa:
        // 'alojamiento', 'tipo_alojamiento' o 'paquete'. Este mapa es lo que
        // traduce entre ese texto y la clase al recorrer las relaciones
        // polimóficas fotos() y recurso().
        Relation::morphMap([
            'alojamiento' => Accommodation::class,
            'tipo_alojamiento' => AccommodationType::class,
            'paquete' => PaqueteTuristico::class,
        ]);
    }
}
