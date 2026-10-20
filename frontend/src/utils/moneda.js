/**
 * Formateo de importes en la moneda de cada país.
 *
 * Antes el frontend escribía "USD" a mano en cada vista y el backend guardaba
 * precios sin ningún código de moneda. Con Bolivia en bolivianos, México en
 * pesos y España en euros, un `185.00` a secas no significaba nada, y el filtro
 * de precio comparaba euros contra bolivianos como si fueran lo mismo.
 *
 * Ahora el backend manda el código ISO 4217 (`currency` en un alojamiento,
 * `moneda_iso` en un país) y este módulo lo convierte en texto.
 */

/**
 * Un code de moneda no puede ser cualquiera: `Intl.NumberFormat` lanza
 * `RangeError` con uno inválido. Un dato malo en la base no debe dejar la
 * tarjeta en blanco, así que el error se captura y se cae al código suelto.
 *
 * Además los decimales los decide la moneda, no el código: el peso colombiano
 * y el yen no llevan decimales, el boliviano y el europeo sí. Por eso no se
 * fijan `minimumFractionDigits` ni `maximumFractionDigits`: se deja que Intl
 * aplique los de la moneda, que es lo único que los hace correctos.
 *
 * Con 'symbol' el mexicano, el colombiano y el argentino salen los tres como
 * "$", algo que se desambigua mirando el país que ya aparece junto al precio en
 * la tarjeta y en la ficha. Para los textos que lee una máquina o un buscador,
 * `codigo: true` fuerza MXN, COP, ARS en vez de tres "$" iguales.
 */

/**
 * @param {number|string|null|undefined} valor
 * @param {string|null|undefined} moneda  Código ISO 4217, p. ej. 'BOB'.
 * @param {string} idioma  'es' o 'en'.
 * @param {{codigo?: boolean}} opciones
 * @returns {string}
 */
export function formatearMoneda(valor, moneda, idioma = 'es', opciones = {}) {
  const numero = Number(valor);

  if (valor == null || valor === '' || !Number.isFinite(numero)) {
    return '';
  }

  // Sin moneda conocida (un alojamiento sin país) se muestra el número pelado:
  // inventar un símbolo sería peor que no poner ninguno.
  if (!moneda) {
    return new Intl.NumberFormat(idioma).format(numero);
  }

  try {
    return new Intl.NumberFormat(idioma, {
      style: 'currency',
      currency: moneda,
      currencyDisplay: opciones.codigo ? 'code' : 'symbol',
    }).format(numero);
  } catch {
    return `${new Intl.NumberFormat(idioma).format(numero)} ${moneda}`;
  }
}

/**
 * Igual que `formatearMoneda` pero devuelve el importe sin símbolo ni código, con
 * los decimales de la moneda. Sirve para textos donde el símbolo ya está escrito
 * fuera, como las meta descripciones que el crawler lee.
 */
export function formatearImporte(valor, moneda, idioma = 'es') {
  const numero = Number(valor);

  if (valor == null || valor === '' || !Number.isFinite(numero)) {
    return '';
  }

  try {
    return new Intl.NumberFormat(idioma, {
      style: 'decimal',
      minimumFractionDigits: decimalesDe(moneda),
      maximumFractionDigits: decimalesDe(moneda),
    }).format(numero);
  } catch {
    return new Intl.NumberFormat(idioma).format(numero);
  }
}

/** Decimales que usa una moneda, o 2 si no se reconoce. */
function decimalesDe(moneda) {
  try {
    return new Intl.NumberFormat('es', { style: 'currency', currency: moneda })
      .resolvedOptions()
      .maximumFractionDigits;
  } catch {
    return 2;
  }
}