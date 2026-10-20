/**
 * Genera public/og-default.png, la imagen que se usa en og:image y twitter:image
 * cuando una página no tiene foto propia.
 *
 * Por qué existe: todas las etiquetas apuntaban a /og-default.png, pero el
 * archivo no estaba en public/. Una og:image que devuelve 404 no muestra nada
 * cuando se comparte el enlace en redes sociales, y la portada y las páginas de
 * país son precisamente las que no tienen foto propia.
 *
 * Las medidas 1200x630 son las que recommends Open Graph; Twitter usa las
 * mismas. Si más adelante hay un diseño real, borra este script y deja el PNG en
 * su sitio: el código solo necesita el archivo, no saber cómo se hizo.
 *
 * Uso:  node scripts/generar-og-default.mjs
 */

import { deflateSync } from 'node:zlib';
import { writeFile } from 'node:fs/promises';
import { dirname, join, resolve } from 'node:path';
import { fileURLToPath } from 'node:url';

const RAIZ = resolve(dirname(fileURLToPath(import.meta.url)), '..');
const DESTINO = join(RAIZ, 'public', 'og-default.png');

const ANCHO = 1200;
const ALTO = 630;

/* ------------------------------------------------------------------ */
/* PNG mínimo: firma + IHDR + IDAT + IEND                            */
/* ------------------------------------------------------------------ */

const TABLA_CRC = (() => {
  const tabla = new Int32Array(256);

  for (let n = 0; n < 256; n += 1) {
    let c = n;

    for (let k = 0; k < 8; k += 1) {
      c = c & 1 ? 0xedb88320 ^ (c >>> 1) : c >>> 1;
    }

    tabla[n] = c;
  }

  return tabla;
})();

function crc32(buffer) {
  let c = 0xffffffff;

  for (let i = 0; i < buffer.length; i += 1) {
    c = TABLA_CRC[(c ^ buffer[i]) & 0xff] ^ (c >>> 8);
  }

  return (c ^ 0xffffffff) >>> 0;
}

function trozo(tipo, datos) {
  const largo = Buffer.alloc(4);
  largo.writeUInt32BE(datos.length, 0);

  // El CRC cubre el tipo y los datos, pero no el propio largo.
  const cuerpo = Buffer.concat([Buffer.from(tipo, 'ascii'), datos]);

  const crc = Buffer.alloc(4);
  crc.writeUInt32BE(crc32(cuerpo), 0);

  return Buffer.concat([largo, cuerpo, crc]);
}

function codificaPng(pixels) {
  const firma = Buffer.from([0x89, 0x50, 0x4e, 0x47, 0x0d, 0x0a, 0x1a, 0x0a]);

  const ihdr = Buffer.alloc(13);
  ihdr.writeUInt32BE(ANCHO, 0);
  ihdr.writeUInt32BE(ALTO, 4);
  ihdr[8] = 8; // 8 bits por canal
  ihdr[9] = 2; // color type 2 = truecolor RGB, sin canal alfa
  ihdr[10] = 0; // compresión deflate
  ihdr[11] = 0; // filtro adaptativo
  ihdr[12] = 0; // sin entrelazado

  // Cada scanline lleva delante su byte de filtro; se usa 0 (sin filtro), que
  // comprime bien cuando la imagen es un degradado suave.
  const filas = Buffer.alloc(ALTO * (1 + ANCHO * 3));

  for (let y = 0; y < ALTO; y += 1) {
    const inicio = y * (1 + ANCHO * 3);
    filas[inicio] = 0;
    pixels.copy(filas, inicio + 1, y * ANCHO * 3, (y + 1) * ANCHO * 3);
  }

  return Buffer.concat([
    firma,
    trozo('IHDR', ihdr),
    trozo('IDAT', deflateSync(filas, { level: 9 })),
    trozo('IEND', Buffer.alloc(0)),
  ]);
}

/* ------------------------------------------------------------------ */
/* Dibujo                                                              */
/* ------------------------------------------------------------------ */

const ROSE = [225, 29, 72]; // rose-600
const PINK = [219, 39, 119]; // pink-600
const VINO = [76, 5, 25]; // rose-950, para el peso visual de la marca

function mezcla(a, b, t) {
  return [
    Math.round(a[0] + (b[0] - a[0]) * t),
    Math.round(a[1] + (b[1] - a[1]) * t),
    Math.round(a[2] + (b[2] - a[2]) * t),
  ];
}

function dibuja() {
  const pixeles = Buffer.alloc(ALTO * ANCHO * 3);

  // Centro del degradado radial, desplazado para que la luz no quede centrada.
  const cx = ANCHO * 0.32;
  const cy = ALTO * 0.28;

  for (let y = 0; y < ALTO; y += 1) {
    for (let x = 0; x < ANCHO; x += 1) {
      // Degradado diagonal de rosa a vino, más un halo claro hacia el foco.
      const diagonal = (x / ANCHO) * 0.45 + (y / ALTO) * 0.55;
      const base = mezcla(ROSE, PINK, Math.min(1, diagonal * 0.8));

      const dx = x - cx;
      const dy = y - cy;
      const distancia = Math.sqrt(dx * dx + dy * dy) / (ALTO * 1.15);
      const halo = Math.max(0, 1 - distancia) ** 2 * 0.45;

      const color = mezcla(base, [255, 240, 245], halo);

      // Velo oscuro hacia el borde inferior derecho: da profundidad sin
      // necesidad de sombras por píxel.
      const profundidad = ((x / ANCHO) * 0.5 + (y / ALTO) * 0.5) ** 2 * 0.5;
      const final = mezcla(color, VINO, profundidad);

      const i = (y * ANCHO + x) * 3;
      pixeles[i] = final[0];
      pixeles[i + 1] = final[1];
      pixeles[i + 2] = final[2];
    }
  }

  /*
   * Tres rectángulos translúcidos que sugieren una fachada de ventanas. Se dibujan
   * a mano porque no hay fuente disponible en este entorno; la idea es dar una
   * silueta reconocible de alojamiento sin depender de un archivo de diseño.
   */
  laEdificios();
  return pixeles;

  function laEdificio(x0, y0, w, h, luz) {
    for (let y = y0; y < y0 + h; y += 1) {
      for (let x = x0; x < x0 + w; x += 1) {
        if (x < 0 || y < 0 || x >= ANCHO || y >= ALTO) continue;

        const i = (y * ANCHO + x) * 3;
        // Las ventanas sealternatean para que se lean como una rejilla.
        const ventana = (Math.floor((x - x0) / 14) + Math.floor((y - y0) / 18)) % 2 === 0;
        const destino = ventana ? [255, 255, 255] : [255, 220, 232];

        pixeles[i] = Math.round(pixeles[i] * (1 - luz) + destino[0] * luz);
        pixeles[i + 1] = Math.round(pixeles[i + 1] * (1 - luz) + destino[1] * luz);
        pixeles[i + 2] = Math.round(pixeles[i + 2] * (1 - luz) + destino[2] * luz);
      }
    }
  }

  function laEdificios() {
    laEdificio(120, 250, 150, 240, 0.14);
    laEdificio(300, 190, 190, 300, 0.2);
    laEdificio(520, 280, 130, 210, 0.12);
    laEdificio(680, 160, 200, 330, 0.22);
    laEdificio(910, 270, 150, 220, 0.13);
  }
}

const png = codificaPng(dibuja());

await writeFile(DESTINO, png);

console.log(`[og-default] ${DESTINO} (${ANCHO}x${ALTO}, ${(png.length / 1024).toFixed(1)} kB)`);
