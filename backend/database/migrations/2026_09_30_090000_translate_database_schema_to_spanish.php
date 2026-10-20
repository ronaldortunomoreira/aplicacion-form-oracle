<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Columnas que Laravel crea como JSON. MariaDB las almacena como longtext,
     * así que hay que volver a declararlas como json para no perder la
     * validación json_valid() del motor.
     */
    private const COLUMNAS_JSON = [
        'amenities' => true,
        'policies' => true,
        'images' => true,
        'bed_distribution' => true,
        'amenidades' => true,
        'politicas' => true,
        'imagenes' => true,
        'distribucion_camas' => true,
    ];

    /**
     * Tipos cuyo valor por defecto no debe entrecomillarse.
     */
    private const TIPOS_NUMERICOS = [
        'tinyint', 'smallint', 'mediumint', 'int', 'integer', 'bigint',
        'decimal', 'float', 'double', 'real', 'bit', 'year',
    ];

    /**
     * Restricciones de clave foránea que hay que rehacer: MySQL no tiene
     * RENAME CONSTRAINT, y ni ALTER TABLE ... CHANGE ni RENAME TABLE tocan el
     * nombre de la restricción, así que sobreviverían en inglés.
     *
     * Cada entrada declara además la acción al borrar, que se reproduce tal cual
     * para no alterar la integridad referencial.
     */
    private const CLAVES_FORANEAS = [
        'alojamientos' => [
            'accommodations_accommodation_type_id_foreign' => [
                'nombre' => 'alojamientos_tipo_alojamiento_id_foreign',
                'columna' => 'tipo_alojamiento_id',
                'referencia' => 'tipos_alojamiento',
                'alBorrar' => 'cascade',
            ],
        ],
        'unidades_habitacion' => [
            'room_units_accommodation_id_foreign' => [
                'nombre' => 'unidades_habitacion_alojamiento_id_foreign',
                'columna' => 'alojamiento_id',
                'referencia' => 'alojamientos',
                'alBorrar' => 'cascade',
            ],
        ],
        'reservas' => [
            'reservations_room_unit_id_foreign' => [
                'nombre' => 'reservas_unidad_habitacion_id_foreign',
                'columna' => 'unidad_habitacion_id',
                'referencia' => 'unidades_habitacion',
                'alBorrar' => 'restrict',
            ],
        ],
    ];

    /**
     * Traduce al español las tablas, columnas e índices del dominio de HospedaYa.
     *
     * Solo se renombran objetos: no se reescribe ningún dato, por lo que las
     * reservas ya registradas sobreviven intactas. Los valores de los ENUM y
     * las claves del JSON de la API se mantienen en inglés a propósito, para no
     * romper el frontend React.
     *
     * Las columnas se renombran con ALTER TABLE ... CHANGE en lugar de usar
     * renameColumn() porque en MariaDB 10.4 (XAMPP) Laravel cae a esa variante y
     * vuelve a entrecomillar el valor por defecto que el motor ya devuelve
     * entrecomillado: `default '''15:00'''`. Con eso las columnas varchar
     * quedan con comillas dentro del valor y las de enum fallan outright.
     */
    public function up(): void
    {
        $this->renombrarColumnas('accommodation_types', [
            'name' => 'nombre',
            'icon' => 'icono',
            'description' => 'descripcion',
        ]);

        // MariaDB 10.4 no soporta RENAME INDEX: se recrean las restricciones.
        Schema::table('accommodation_types', function (Blueprint $table) {
            $table->unique('slug', 'tipos_alojamiento_slug_unique');
            $table->dropUnique('accommodation_types_slug_unique');
        });

        $this->renombrarColumnas('accommodations', [
            'accommodation_type_id' => 'tipo_alojamiento_id',
            'name' => 'nombre',
            'headline' => 'titulo',
            'description' => 'descripcion',
            'address' => 'direccion',
            'city' => 'ciudad',
            'state' => 'provincia',
            'country' => 'pais',
            'latitude' => 'latitud',
            'longitude' => 'longitud',
            'star_rating' => 'estrellas',
            'guest_rating' => 'valoracion_huesped',
            'reviews_count' => 'numero_resenas',
            'check_in_time' => 'hora_entrada',
            'check_out_time' => 'hora_salida',
            'amenities' => 'amenidades',
            'policies' => 'politicas',
            'images' => 'imagenes',
            'status' => 'estado',
        ]);

        Schema::table('accommodations', function (Blueprint $table) {
            $table->unique('slug', 'alojamientos_slug_unique');
            $table->index('ciudad', 'alojamientos_ciudad_index');
            $table->index(['ciudad', 'tipo_alojamiento_id', 'estado'], 'idx_alojamientos_busqueda');
            $table->index('valoracion_huesped', 'idx_alojamientos_valoracion');
        });

        Schema::table('accommodations', function (Blueprint $table) {
            $table->dropUnique('accommodations_slug_unique');
            $table->dropIndex('accommodations_city_index');
            $table->dropIndex('idx_accommodations_search');
            $table->dropIndex('idx_accommodations_rating');
        });

        $this->renombrarColumnas('room_units', [
            'accommodation_id' => 'alojamiento_id',
            'name' => 'nombre',
            'unit_type' => 'tipo_unidad',
            'capacity_adults' => 'capacidad_adultos',
            'capacity_children' => 'capacidad_ninos',
            'total_units' => 'total_unidades',
            'base_price_per_night' => 'precio_base_noche',
            'cleaning_fee' => 'tarifa_limpieza',
            'bathroom_type' => 'tipo_bano',
            'bed_distribution' => 'distribucion_camas',
            'amenities' => 'amenidades',
            'images' => 'imagenes',
        ]);

        // Este índice también sostiene la clave foránea: se crea el nuevo antes
        // de eliminar el antiguo, porque MySQL rechaza dejar a una FK sin índice.
        Schema::table('room_units', function (Blueprint $table) {
            $table->index(['alojamiento_id', 'precio_base_noche'], 'idx_unidades_alojamiento_precio');
        });

        Schema::table('room_units', function (Blueprint $table) {
            $table->dropIndex('room_units_accommodation_id_base_price_per_night_index');
        });

        $this->renombrarColumnas('reservations', [
            'reservation_code' => 'codigo_reserva',
            'room_unit_id' => 'unidad_habitacion_id',
            'guest_name' => 'nombre_huesped',
            'guest_email' => 'correo_huesped',
            'guest_phone' => 'telefono_huesped',
            'guest_document_id' => 'documento_huesped',
            'check_in_date' => 'fecha_entrada',
            'check_out_date' => 'fecha_salida',
            'adults_count' => 'adultos',
            'children_count' => 'ninos',
            'nights' => 'noches',
            'price_per_night' => 'precio_noche',
            'cleaning_fee' => 'tarifa_limpieza',
            'taxes' => 'impuestos',
            'total_price' => 'precio_total',
            'payment_method' => 'metodo_pago',
            'payment_status' => 'estado_pago',
            'reservation_status' => 'estado_reserva',
            'special_requests' => 'solicitudes_especiales',
            'cancellation_reason' => 'motivo_cancelacion',
            'cancelled_at' => 'cancelada_en',
        ]);

        Schema::table('reservations', function (Blueprint $table) {
            $table->unique('codigo_reserva', 'reservas_codigo_reserva_unique');
            $table->index('correo_huesped', 'reservas_correo_huesped_index');
            $table->index('fecha_entrada', 'reservas_fecha_entrada_index');
            $table->index('fecha_salida', 'reservas_fecha_salida_index');
            $table->index(
                ['unidad_habitacion_id', 'estado_reserva', 'fecha_entrada', 'fecha_salida'],
                'idx_disponibilidad_reserva'
            );
        });

        Schema::table('reservations', function (Blueprint $table) {
            $table->dropUnique('reservations_reservation_code_unique');
            $table->dropIndex('reservations_guest_email_index');
            $table->dropIndex('reservations_check_in_date_index');
            $table->dropIndex('reservations_check_out_date_index');
            $table->dropIndex('idx_res_availability');
        });

        // Al final: RENAME TABLE actualiza por su cuenta las referencias de las
        // claves foráneas, así que el orden de los rename es irrelevante.
        Schema::rename('accommodations', 'alojamientos');
        Schema::rename('room_units', 'unidades_habitacion');
        Schema::rename('reservations', 'reservas');
        Schema::rename('accommodation_types', 'tipos_alojamiento');

        // Las restricciones se renombran al final, cuando las tablas ya tienen su
        // nombre definitivo y la columna referenciada es la que queda.
        $this->renombrarClavesForaneas();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Antes de deshacer los rename de tabla, para que las restricciones
        // vuelvan a apuntar a las tablas que todavía están en español.
        $this->renombrarClavesForaneas();

        Schema::rename('tipos_alojamiento', 'accommodation_types');
        Schema::rename('reservas', 'reservations');
        Schema::rename('unidades_habitacion', 'room_units');
        Schema::rename('alojamientos', 'accommodations');

        $this->renombrarColumnas('accommodation_types', [
            'nombre' => 'name',
            'icono' => 'icon',
            'descripcion' => 'description',
        ]);

        Schema::table('accommodation_types', function (Blueprint $table) {
            $table->unique('slug', 'accommodation_types_slug_unique');
            $table->dropUnique('tipos_alojamiento_slug_unique');
        });

        $this->renombrarColumnas('accommodations', [
            'tipo_alojamiento_id' => 'accommodation_type_id',
            'nombre' => 'name',
            'titulo' => 'headline',
            'descripcion' => 'description',
            'direccion' => 'address',
            'ciudad' => 'city',
            'provincia' => 'state',
            'pais' => 'country',
            'latitud' => 'latitude',
            'longitud' => 'longitude',
            'estrellas' => 'star_rating',
            'valoracion_huesped' => 'guest_rating',
            'numero_resenas' => 'reviews_count',
            'hora_entrada' => 'check_in_time',
            'hora_salida' => 'check_out_time',
            'amenidades' => 'amenities',
            'politicas' => 'policies',
            'imagenes' => 'images',
            'estado' => 'status',
        ]);

        Schema::table('accommodations', function (Blueprint $table) {
            $table->unique('slug', 'accommodations_slug_unique');
            $table->index('city', 'accommodations_city_index');
            $table->index(['city', 'accommodation_type_id', 'status'], 'idx_accommodations_search');
            $table->index('guest_rating', 'idx_accommodations_rating');
        });

        Schema::table('accommodations', function (Blueprint $table) {
            $table->dropUnique('alojamientos_slug_unique');
            $table->dropIndex('alojamientos_ciudad_index');
            $table->dropIndex('idx_alojamientos_busqueda');
            $table->dropIndex('idx_alojamientos_valoracion');
        });

        $this->renombrarColumnas('room_units', [
            'alojamiento_id' => 'accommodation_id',
            'nombre' => 'name',
            'tipo_unidad' => 'unit_type',
            'capacidad_adultos' => 'capacity_adults',
            'capacidad_ninos' => 'capacity_children',
            'total_unidades' => 'total_units',
            'precio_base_noche' => 'base_price_per_night',
            'tarifa_limpieza' => 'cleaning_fee',
            'tipo_bano' => 'bathroom_type',
            'distribucion_camas' => 'bed_distribution',
            'amenidades' => 'amenities',
            'imagenes' => 'images',
        ]);

        Schema::table('room_units', function (Blueprint $table) {
            $table->index(
                ['accommodation_id', 'base_price_per_night'],
                'room_units_accommodation_id_base_price_per_night_index'
            );
            $table->dropIndex('idx_unidades_alojamiento_precio');
        });

        $this->renombrarColumnas('reservations', [
            'codigo_reserva' => 'reservation_code',
            'unidad_habitacion_id' => 'room_unit_id',
            'nombre_huesped' => 'guest_name',
            'correo_huesped' => 'guest_email',
            'telefono_huesped' => 'guest_phone',
            'documento_huesped' => 'guest_document_id',
            'fecha_entrada' => 'check_in_date',
            'fecha_salida' => 'check_out_date',
            'adultos' => 'adults_count',
            'ninos' => 'children_count',
            'noches' => 'nights',
            'precio_noche' => 'price_per_night',
            'tarifa_limpieza' => 'cleaning_fee',
            'impuestos' => 'taxes',
            'precio_total' => 'total_price',
            'metodo_pago' => 'payment_method',
            'estado_pago' => 'payment_status',
            'estado_reserva' => 'reservation_status',
            'solicitudes_especiales' => 'special_requests',
            'motivo_cancelacion' => 'cancellation_reason',
            'cancelada_en' => 'cancelled_at',
        ]);

        Schema::table('reservations', function (Blueprint $table) {
            $table->unique('reservation_code', 'reservations_reservation_code_unique');
            $table->index('guest_email', 'reservations_guest_email_index');
            $table->index('check_in_date', 'reservations_check_in_date_index');
            $table->index('check_out_date', 'reservations_check_out_date_index');
            $table->index(
                ['room_unit_id', 'reservation_status', 'check_in_date', 'check_out_date'],
                'idx_res_availability'
            );
        });

        Schema::table('reservations', function (Blueprint $table) {
            $table->dropUnique('reservas_codigo_reserva_unique');
            $table->dropIndex('reservas_correo_huesped_index');
            $table->dropIndex('reservas_fecha_entrada_index');
            $table->dropIndex('reservas_fecha_salida_index');
            $table->dropIndex('idx_disponibilidad_reserva');
        });
    }

    /**
     * Renombra las restricciones de clave foránea declaradas en
     * self::CLAVES_FORANEAS para dejarlas en español al terminar up(), y otra vez
     * en inglés al terminar down().
     *
     * MySQL no tiene RENAME CONSTRAINT, así que hay que soltar la restricción y
     * volver a declararla. Se llama antes de renombrar tablas y columnas en
     * down(), de modo que la columna y la tabla referenciada todavía son las del
     * mapa y no hace falta traducirlas.
     */
    private function renombrarClavesForaneas(): void
    {
        foreach (self::CLAVES_FORANEAS as $tabla => $restricciones) {
            foreach ($restricciones as $nombreIngles => $traduccion) {
                $nombreEspanol = $traduccion['nombre'];

                // La misma constante sirve para los dos sentidos, así que el
                // sentido se deduce de cuál de los dos nombres existe ahora.
                $haciaIngles = ! $this->tieneRestriccion($tabla, $nombreIngles)
                    && $this->tieneRestriccion($tabla, $nombreEspanol);

                $desde = $haciaIngles ? $nombreEspanol : $nombreIngles;
                $hasta = $haciaIngles ? $nombreIngles : $nombreEspanol;

                if (! $this->tieneRestriccion($tabla, $desde)) {
                    continue;
                }

                DB::statement("alter table `$tabla` drop foreign key `$desde`");

                // InnoDB crea un índice con el nombre de la restricción para
                // sostenerla. Si ya había otro que sirviera, este no existe; y si
                // existe y queda huérfano hay que retirarlo para que no sobreviva
                // en inglés. Se suelta antes de volver a declarar la FK.
                if (Schema::hasIndex($tabla, $desde)) {
                    DB::statement("alter table `$tabla` drop index `$desde`");
                }

                DB::statement(
                    "alter table `$tabla` add constraint `$hasta`"
                    ." foreign key (`{$traduccion['columna']}`)"
                    ." references `{$traduccion['referencia']}` (`id`)"
                    ." on delete {$traduccion['alBorrar']} on update restrict"
                );
            }
        }
    }

    private function tieneRestriccion(string $tabla, string $restriccion): bool
    {
        return DB::table('information_schema.referential_constraints')
            ->where('constraint_schema', DB::getDatabaseName())
            ->where('table_name', $tabla)
            ->where('constraint_name', $restriccion)
            ->exists();
    }

    /**
     * Renombra columnas conservando su definición original.
     *
     * No se puede usar renameColumn() aquí (ver la nota de up()): en MariaDB
     * 10.4 Laravel recompone la definición y rompe los valores por defecto.
     * Se lee la definición real de cada columna y se emite el CHANGE con el
     * default correctamente entrecomillado.
     */
    private function renombrarColumnas(string $tabla, array $mapa): void
    {
        $columnas = collect(Schema::getColumns($tabla))->keyBy('name');

        foreach ($mapa as $desde => $hasta) {
            if (! $columnas->has($desde)) {
                continue;
            }

            $columna = $columnas[$desde];
            $esJson = isset(self::COLUMNAS_JSON[$desde]);

            $definicion = '`'.$hasta.'` '.($esJson ? 'json' : $columna['type']);

            if ($columna['collation'] !== null && ! $esJson && $columna['type_name'] !== 'enum') {
                $definicion .= ' collate '.$columna['collation'];
            }

            $definicion .= $columna['nullable'] ? ' null' : ' not null';

            $porDefecto = $columna['default'];

            if ($porDefecto !== null && $porDefecto !== 'NULL') {
                // MariaDB devuelve '15:00' y 'active' ya entrecomillados; si se
                // vuelven a entrecomillar el valor acaba con comillas dentro.
                if (str_starts_with($porDefecto, "'") && str_ends_with($porDefecto, "'")) {
                    $porDefecto = substr($porDefecto, 1, -1);
                }

                $esNumerico = in_array($columna['type_name'], self::TIPOS_NUMERICOS, true);

                $definicion .= ' default '.($esNumerico
                    ? $porDefecto
                    : "'".addcslashes($porDefecto, "'")."'");
            }

            if ($columna['auto_increment']) {
                $definicion .= ' auto_increment';
            }

            DB::statement("alter table `$tabla` change `$desde` $definicion");
        }
    }
};
