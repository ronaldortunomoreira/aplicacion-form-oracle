<?php

namespace App\Models\Concerns;

trait TraduceColumnas
{
    /**
     * Traduce el atributo del modelo (inglés, dominio) al nombre real de la
     * columna (español, base de datos) al leerlo.
     */
    public function getAttribute($key)
    {
        return parent::getAttribute($this->nombreColumna($key));
    }

    /**
     * Traduce la clave al escribir, de modo que create()/update() sigan
     * recibiendo los nombres en inglés declarados en $fillable.
     */
    public function setAttribute($key, $value)
    {
        return parent::setAttribute($this->nombreColumna($key), $value);
    }

    /**
     * Devuelve las claves en inglés al serializar, para que el contrato JSON
     * de la API no cambie y el frontend React siga funcionando sin ajustes.
     */
    public function attributesToArray()
    {
        $atributos = parent::attributesToArray();
        $inverso = array_flip(static::$columnas);

        $traducidos = [];

        foreach ($atributos as $columna => $valor) {
            $traducidos[$inverso[$columna] ?? $columna] = $valor;
        }

        return $traducidos;
    }

    /**
     * Atributos calculados que el modelo añade en caliente (starting_price,
     * available_count, estimated_total...) no son columnas reales, así que
     * deben viajar tal cual en la respuesta JSON.
     */
    protected function nombreColumna(string $clave): string
    {
        return static::$columnas[$clave] ?? $clave;
    }
}
