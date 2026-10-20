<?php

namespace App\Models;

use App\Models\Concerns\TraduceColumnas;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AccommodationType extends Model
{
    use HasFactory;
    use TraduceColumnas;

    /**
     * Carpeta pública de fotos de cada tipo, dentro de images/alojamientos/.
     *
     * El slug es el mismo que viaja en la URL (/alojamientos?type_slug=hotel) y
     * el nombre de carpeta es la forma plural y legible con la que el
     * administrador organiza los archivos en disco: public/images/alojamientos/
     * hoteles/, public/images/alojamientos/hostales/...
     */
    public const CARPETAS = [
        'hotel' => 'hoteles',
        'hostal' => 'hostales',
        'cabana' => 'cabanas',
        'apartamento' => 'apartamentos',
        'glamping' => 'glamping',
    ];

    protected $table = 'tipos_alojamiento';

    protected static $columnas = [
        'name' => 'nombre',
        'icon' => 'icono',
        'description' => 'descripcion',
    ];

    protected $fillable = [
        'name',
        'slug',
        'icon',
        'description',
    ];

    public function accommodations(): HasMany
    {
        return $this->hasMany(Accommodation::class, 'tipo_alojamiento_id');
    }

    /**
     * Carpeta pública que guarda las fotos de este tipo, relativa a public/.
     *
     * Es la galería que hereda todo alojamiento de este tipo que no tenga fotos
     * propias. Un tipo sin carpeta en la constante usa su slug, para no dejarlo
     * sin galería.
     */
    public function carpetaFotos(): string
    {
        return 'images/alojamientos/' . (self::CARPETAS[$this->slug] ?? $this->slug);
    }
}
