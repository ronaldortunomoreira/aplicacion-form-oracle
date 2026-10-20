import { separaIdioma, rutaConIdioma, IDIOMAS_ACTIVOS } from './src/seo/idiomas.js';

const casos = [
  '/es/paises/espana',
  '/en/paises/espana',
  '/en/alojamiento/grand-palace-luxury-hotel-spa',
  '/en',
  '/es',
  '/',
];

console.log('construccion de enlaces del selector:');
for (const pathname of casos) {
  const { ruta } = separaIdioma(pathname);
  const enlaces = IDIOMAS_ACTIVOS.map((i) => `/${i.codigo}${ruta === '/' ? '' : ruta}`);
  const activoActual = pathname.match(/^\/([a-z]{2})/)?.[1] || 'es';
  console.log(`  ${pathname}`);
  console.log(`     ruta interna: ${ruta}`);
  console.log(`     enlaces: ${enlaces.join('  |  ')}   activo: ${activoActual}`);
}

console.log('\nrutaConIdioma conservando ruta:');
for (const c of ['es', 'en']) {
  console.log(`  ${c}: ${rutaConIdioma('/paises/espana', c)}  ${rutaConIdioma('/', c)}`);
}
