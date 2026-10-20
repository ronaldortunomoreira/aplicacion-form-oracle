<?php

namespace App\Services;

use Giggsey\Locale\Locale;
use Illuminate\Support\Facades\Cache;
use libphonenumber\NumberParseException;
use libphonenumber\PhoneNumberFormat;
use libphonenumber\PhoneNumberType;
use libphonenumber\PhoneNumberUtil;

/**
 * Catálogo de países y reglas de WhatsApp de los formularios públicos.
 *
 * Los datos vienen de libphonenumber, que replica los metadatos de la UIT, en
 * lugar de una tabla escrita a mano. Eso cubre 245 regiones con su prefijo, sus
 * longitudes y sus patrones, y sobre todo resuelve el detalle que una lista
 * propia no acertaría: los prefijos troncales. El visitante escribe su número
 * como lo conoce, "030 1234567" en Alemania o "07 1234 5678" en México, y el 0
 * inicial no forma parte del número internacional, así que el mismo número
 * escrito de dos formas tiene que acabar guardado igual. Esa diferencia depende
 * de cada país y además cambia entre versiones del plan de numeración, así que
 * se deja que la librería la aplique en lugar de calcularla aquí.
 *
 * Lo consumen el alta de hotel y la reserva. Ambos formularios piden el país para
 * poder localizar el teléfono, pero cada uno lo guarda en su propia clave:
 *
 *   - `empresas.codigo_pais` guarda el ISO2, porque allí el país identifica a la
 *     empresa y hace falta distinguir las 24 regiones que comparten el +1.
 *   - `reservas.codigo_pais` guarda el prefijo, porque allí el país solo sirve para
 *     marcar el teléfono y el prefijo es lo que se necesita para marcarlo.
 *
 * El catálogo se usa solo en /registro-hotel y en el modal de reserva.
 * `GET /api/countries` sigue saliendo de la tabla `paises`, porque alimenta el
 * buscador y las páginas /paises/<slug>, que deben seguir mostrando únicamente los
 * países con alojamientos publicados.
 */
class CatalogoPaises
{
    /**
     * Reglas de un país, listas para pintar el selector del formulario.
     *
     * Cada entrada lleva un número de móvil de ejemplo ya escrito en formato
     * nacional: sirve de placeholder y da al visitante una referencia de la
     * longitud que espera su país, que es lo único que cambia entre regiones.
     *
     * El resultado se cachea porque solo depende de los metadatos de la
     * librería: los 245 ejemplos son idénticos en cada petición y no hay nada
     * que sincronizar con la base de datos.
     */
    public function todos(string $idioma = 'es'): array
    {
        $idioma = $idioma === 'en' ? 'en' : 'es';

        return Cache::rememberForever("catalogo-paises-{$idioma}", function () use ($idioma) {
            $util = PhoneNumberUtil::getInstance();
            $nombres = Locale::getAllCountriesForLocale($idioma);

            $paises = [];

            foreach ($util->getSupportedRegions() as $iso2) {
                // Sin ejemplo de móvil se cae al número genérico de la región:
                // casi todas las regiones tienen los dos, pero no todas.
                $ejemplo = $util->getExampleNumberForType($iso2, PhoneNumberType::MOBILE)
                    ?? $util->getExampleNumber($iso2);

                if ($ejemplo === null) {
                    continue;
                }

                $paises[] = [
                    'iso2' => $iso2,
                    'name' => $nombres[$iso2] ?? $iso2,
                    'dial_code' => '+' . $util->getCountryCodeForRegion($iso2),
                    'phone_example' => $util->format($ejemplo, PhoneNumberFormat::NATIONAL),
                ];
            }

            // Orden alfabético en el idioma del visitante: "México" no va en la
            // M de México sino junto a los demás nombres en español.
            $collator = new \Collator($idioma);
            usort($paises, fn (array $a, array $b) => $collator->compare($a['name'], $b['name']));

            return $paises;
        });
    }

    /**
     * Códigos ISO2 admitidos, para la regla `in:` de la validación.
     */
    public function codigos(): array
    {
        return PhoneNumberUtil::getInstance()->getSupportedRegions();
    }

    /**
     * Normaliza un número de WhatsApp al formato en el que se guarda.
     *
     * Devuelve el número nacional sin prefijo internacional ni prefijo troncal,
     * o null si el número no es válido para ese país. El prefijo no se guarda
     * porque `codigo_pais` ya lo determina y duplicarlo haría que las dos
     * columnas pudieran contradecirse.
     *
     * Se acepta el número como lo escriba el visitante: "600 123 456",
     * "+34 600 123 456" o "+34 (600) 12-34-56" dan el mismo resultado.
     *
     * La validación comprueba que el número pertenezca al país elegido. Sin
     * ese filtro, quien seleccionara España y escribiera un +49 alemán lo
     * tendría aceptado y guardado bajo el prefijo español, y el enlace de
     * WhatsApp apuntaría al número equivocado.
     */
    public function normalizar(string $numero, string $iso2): ?string
    {
        $util = PhoneNumberUtil::getInstance();

        try {
            $parseado = $util->parse($numero, $iso2);
        } catch (NumberParseException) {
            return null;
        }

        if ($parseado->getCountryCode() !== $util->getCountryCodeForRegion($iso2)) {
            return null;
        }

        // isValidNumber() exige que el número exista en el plan de numeración
        // del país, con su longitud y su prefijo móvil correctos. No se exige
        // además que sea de tipo móvil porque hay regiones (Estados Unidos,
        // Canadá) donde la UIT no distingue el móvil de la línea fija, y el
        // filtro llegaría a rechazar todos los números.
        if (!$util->isValidNumber($parseado)) {
            return null;
        }

        return $parseado->getNationalNumber();
    }

    /**
     * Prefijo internacional de un país, por si hay que mostrarlo junto al
     * número guardado.
     */
    public function dialDe(string $iso2): ?string
    {
        if (!in_array($iso2, $this->codigos(), true)) {
            return null;
        }

        return '+' . PhoneNumberUtil::getInstance()->getCountryCodeForRegion($iso2);
    }
}