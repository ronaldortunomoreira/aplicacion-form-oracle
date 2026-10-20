<?php

namespace Database\Seeders;

use App\Models\TravelPackage;
use App\Models\Destination;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class TravelPackageSeeder extends Seeder
{
    public function run(): void
    {
        foreach (Destination::where("destacado", true)->take(20)->get() as $d) {
            $slug = "tour-" . Str::slug($d->nombre) . "-1-dia";
            if (TravelPackage::where("slug", $slug)->exists()) {
                continue;
            }
            $moneda = "USD";
            if ($d->country && $d->country->moneda_iso) {
                $moneda = $d->country->moneda_iso;
            }
            TravelPackage::create([
                "pais_id" => $d->pais_id,
                "destino_id" => $d->id,
                "nombre" => "Tour " . $d->nombre . " 1 Dia",
                "slug" => $slug,
                "ciudad" => $d->ciudad,
                "texto_presentacion" => ["es" => "Experiencia completa.", "en" => "Full experience."],
                "incluye" => ["Guia", "Transporte"],
                "no_incluye" => ["Comidas"],
                "itinerario" => [["hora" => "09:00", "actividad" => "Inicio"]],
                "duracion" => "1 dia",
                "min_personas" => 1,
                "max_personas" => 12,
                "precio_desde" => 500,
                "moneda_iso" => $moneda,
                "imagen" => $d->imagen,
                "destacado" => true,
                "activo" => true,
            ]);
        }
    }
}
