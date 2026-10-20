import { MdPlace } from 'react-icons/md';
import { copy } from '../seo/copys';
import { IDIOMAS_ACTIVOS } from '../seo/idiomas';

/**
 * Mapa de Google con la ubicación del alojamiento, en la ficha.
 *
 * ------------------------------------------------------------------
 * AVISO IMPORTANTE SOBRE EL CANAL QUE USA ESTE MAPA
 * ------------------------------------------------------------------
 * Google Maps no tiene una vía gratuita con clave: la JS API y la Embed
 * API exigen clave **y** facturación activa en un proyecto de Google Cloud,
 * y sin facturación el mapa sale con la marca de agua "solo para
 * desarrollo". Por eso se usa el `output=embed` de `maps.google.com`, que
 * responde con un iframe y no necesita ni clave ni tarjeta:
 *
 *     https://maps.google.com/maps?q=LAT,LNG&z=16&output=embed
 *
 * El precio es que no es un canal soportado ni documentado por Google, y
 * puede dejar de funcionar sin previo aviso.
 *
 * Si algún día hace falta el canal soportado, no cuesta dinero: la Embed
 * API tiene tope de uso ilimitado sin coste. Solo habría que cambiar `src`
 * por `https://www.google.com/maps/embed/v1/place?key=CLAVE&q=LAT,LNG`.
 *
 * ------------------------------------------------------------------
 * POR QUÉ NO HAY UN BOTÓN DE "CÓMO LLEGAR" AQUÍ
 * ------------------------------------------------------------------
 * Se quitó a petición del usuario porque Tardaba demasiado en abrir. El
 * problema no era Google sino lo que había alrededor: para pasarle la
 * ubicación del visitante como `origin` había que pedirle la geolocalización
 * al navegador, y eso implica un aviso de permiso y esperar a que la posición
 * esté fixada. Durante esa espera se abría una pestaña en blanco, porque si se
 * abriera después el bloqueador de popups la cerraría. En la práctica era un
 * clic que no hacía nada visible durante segundos, en una ficha que ya tiene el
 * mapa delante.
 *
 * El enlace en sí no era el problema y sigue siendo una línea si vuelve a
 * hacer falta: `https://www.google.com/maps/dir/?api=1&destination=LAT,LNG`,
 * sin `origin`. Google Maps URL API trata ese parámetro como opcional y, si no
 * se envía, usa por su cuenta "la ubicación más relevante disponible, como la
 * del dispositivo", así que en el móvil ya sale la ruta desde donde está la
 * persona sin pedirle nada a la web.
 */

/**
 * Valida y normaliza las coordenadas que llegan de la API.
 *
 * `Number('')` es 0, y 0,0 es un punto real en el golfo de Guinea, así
 * que un campo vacío o nulo se descarta antes de convertir, no después: si
 * no, un alojamiento sin coordenadas aparecería en medio del océano en lugar
 * de no mostrar mapa.
 */
function normalizarCoordenadas(latitude, longitude) {
  if (latitude === null || latitude === undefined || latitude === '') return null;
  if (longitude === null || longitude === undefined || longitude === '') return null;

  const lat = Number(latitude);
  const lng = Number(longitude);

  if (!Number.isFinite(lat) || !Number.isFinite(lng)) return null;
  if (lat < -90 || lat > 90 || lng < -180 || lng > 180) return null;

  return { lat, lng };
}

export default function MapaGoogle({ accommodation, textos = null, idioma = 'es' }) {
  // El componente se usa desde la ficha, que ya tiene el copy del idioma
  // activo. El fallback evita depender de esa propiedad y deja el mapa
  // utilizable en un test o story aislado.
  const textosMapa = textos?.detalle?.mapa ?? copy(idioma).detalle.mapa;

  // `hl` decide los rótulos del mapa (nombres de calles, controles), así que
  // tiene que seguir al idioma activo como cualquier otro texto de la ficha.
  const hl = IDIOMAS_ACTIVOS.includes(idioma) ? idioma : 'es';

  const coordenadas = normalizarCoordenadas(accommodation?.latitude, accommodation?.longitude);

  if (!coordenadas) {
    return (
      <div>
        <h3 className="text-base font-bold text-slate-900 mb-2">{textosMapa.titulo}</h3>
        <p className="text-xs text-slate-500 italic">{textosMapa.sinCoordenadas}</p>
      </div>
    );
  }

  const consulta = `${coordenadas.lat},${coordenadas.lng}`;

  return (
    <div>
      <div className="mb-3">
        <h3 className="text-base font-bold text-slate-900">{textosMapa.titulo}</h3>
        <p className="text-xs text-slate-500 flex items-center gap-1.5 mt-0.5">
          <MdPlace className="w-3.5 h-3.5 text-rose-500 shrink-0" />
          {accommodation.address}, {accommodation.city}, {accommodation.country}
        </p>
      </div>

      {/*
        El iframe se monta siempre, sin sonda previa ni espera. Un intento
        anterior lo condicionaba a comprobar la red y a un IntersectionObserver,
        y eso dejaba un rectángulo gris vacío durante la comprobación: mejor
        que el mapa no aparezca en redes con Google bloqueado, que es un caso
        raro, que un cuadro en blanco en todas las fichas. `loading="lazy"`
        sigue estando: aplaza la descarga sin aplazar el montaje, y Google ya
        pinta sobre el marco en cuanto lo carga.
      */}
      <div className="rounded-2xl overflow-hidden border border-slate-200 bg-slate-100 aspect-video sm:aspect-[2.4/1]">
        <iframe
          src={`https://maps.google.com/maps?q=${consulta}&z=16&hl=${hl}&output=embed`}
          title={`${textosMapa.titulo}: ${accommodation.name}`}
          loading="lazy"
          referrerPolicy="no-referrer-when-downgrade"
          className="w-full h-full border-0 block"
        />
      </div>
    </div>
  );
}