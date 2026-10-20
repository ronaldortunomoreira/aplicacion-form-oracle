<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Empresa;
use App\Services\CatalogoPaises;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class EmpresaController extends Controller
{
    public function __construct(private CatalogoPaises $catalogo)
    {
    }

    /**
     * Listado público de solicitudes.
     */
    public function index(): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => Empresa::latest()->get(),
        ]);
    }

    /**
     * Países y reglas de WhatsApp para el selector de /registro-hotel.
     *
     * Va aparte de `GET /api/countries` a propósito: aquel endpoint describe el
     * catálogo de países con alojamientos publicados y alimenta el buscador y
     * las páginas /paises/<slug>, mientras que este solo tiene que ofrecer un
     * número de WhatsApp válido a quien quiere darse de alta. Son dos catálogos
     * distintos y mezclarlos añadiría a las páginas de país páginas de países
     * sin ningún alojamiento.
     */
    public function paises(Request $request): JsonResponse
    {
        $paises = $this->catalogo->todos($request->get('lang', 'es'));

        return response()->json([
            'success' => true,
            'count' => count($paises),
            'data' => $paises,
        ]);
    }

    /**
     * Registra una solicitud nueva.
     *
     * Devuelve 201 y la fila creada para que el frontend la añada al listado sin
     * tener que recargarlo.
     */
    public function store(Request $request): JsonResponse
    {
        $datos = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'string', 'email', 'max:150', 'unique:empresas,correo'],
            'country_code' => ['required', 'string', Rule::in($this->catalogo->codigos())],
            // Holgado a propósito: el visitante puede pegar el número con
            // prefijo, espacios y paréntesis. Lo que se guarda es el resultado
            // de normalizarlo, que siempre son pocos dígitos.
            'whatsapp' => ['required', 'string', 'max:30'],
            'detail' => ['nullable', 'string', 'max:2000'],
        ]);

        $digitos = $this->catalogo->normalizar($datos['whatsapp'], $datos['country_code']);

        // La validación de arriba solo comprueba que el campo viene; que el
        // número exista de verdad para ese país lo decide libphonenumber, y el
        // error sale bajo la misma clave para que el frontend pueda mostrar el
        // aviso sin diferenciarlo de los demás errores del formulario.
        if ($digitos === null) {
            throw ValidationException::withMessages([
                'whatsapp' => ['El número de WhatsApp no es válido para el país seleccionado.'],
            ]);
        }

        $empresa = Empresa::create([
            'name' => $datos['name'],
            'email' => $datos['email'],
            'country_code' => $datos['country_code'],
            'whatsapp' => $digitos,
            'detail' => $datos['detail'] ?? null,
        ]);

        return response()->json([
            'success' => true,
            'data' => $empresa,
        ], 201);
    }
}