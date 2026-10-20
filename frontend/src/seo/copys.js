/**
 * Textos de la interfaz y de los metadatos.
 *
 * La plataforma es monolingüe (español), así que todos los textos viven en el
 * bloque `es`. `copy()` los devuelve siempre, y los constructores de metadatos y
 * el prerender los consumen desde el mismo sitio para que la app y el HTML
 * estático no puedan divergir.
 */

import { IDIOMA_POR_DEFECTO } from './idiomas.js';

export const COPYS = {
  es: {
    nombre: 'Español',
    /** Descripción corta de la marca, para el JSON-LD de Organization. */
    descripcion:
      'Reserva hoteles, hostales, cabañas, apartamentos turísticos y glamping. ' +
      'Compara precios, consulta disponibilidad en tiempo real y confirma tu reserva sin cargos ocultos.',
    tituloLargo: 'HospedaYa - Hospedajes y Alojamientos en España y Latinoamérica',

    /** Etiquetas de navegación compartidas, usadas en migas de pan. */
    navegacion: {
      inicio: 'Inicio',
      paises: 'Países',
      destinos: 'Destinos',
      alojamientos: 'Alojamientos',
explorar: 'Explorar Alojamientos',
      misReservas: 'Mis Reservas',
      registrarHotel: 'Registrar alojamiento',
      subtitulo: 'Hoteles, Hostales, Cabañas & Apartamentos',
    },

    /** Conjunción para unir elementos en las listas de la description y el JSON-LD. */
    conjuncion: 'y',

    home: {
      titulo: 'HospedaYa | Hoteles, hostales, cabañas y apartados para reservar',
      h1: 'Reserva hoteles, hostales, cabañas, apartamentos y glamping',
      resumen: (datos) =>
        `Encuentra y reserva tu hospedaje ideal en ${datos.ciudades}. Consulta disponibilidad en ` +
        'tiempo real y confirma tu reserva sin cargos ocultos.',
      tituloAlojamientos: 'Alojamientos disponibles',
      metadatos: (datos) =>
        `${datos.total} hoteles, hostales, cabañas, apartamentos turísticos y glamping. ` +
        `${datos.ciudades}. Confirma tu reserva sin cargos ocultos.`,
      imagenAlt: 'HospedaYa: hoteles, hostales y cabañas para reservar',
      distintivo: 'Alojamientos Verificados • Sin Cargos Ocultos',
      titular: 'Encuentra tu hospedaje ideal,',
      titularResaltado: 'a tu propio estilo y presupuesto',
      entradilla:
        'Hoteles cinco estrellas, hostales llenos de vida, cabañas en la montaña, departamentos ' +
        'turísticos y domos glamping para una estancia inolvidable.',
      encontrados: (datos) => `Alojamientos encontrados (${datos.total})`,
      notaMoneda: 'Precios en la moneda de cada país',
      cargando: 'Buscando alojamientos disponibles...',
      sinResultados: 'No encontramos alojamientos con esos criterios',
      sinResultadosAyuda:
        'Prueba ampliando el rango de precio, cambiando las fechas o seleccionando ' +
        '"Todos los tipos" para ver más opciones.',
      restablecer: 'Restablecer todos los filtros',
    },

    pais: {
      titulo: (datos) => `Alojamientos en ${datos.pais} | ${datos.marca}`,
      h1: (datos) => `Alojamientos en ${datos.pais}`,
      tituloLista: (datos) => `Alojamientos disponibles en ${datos.pais}`,
      sinAlojamientos: (datos) =>
        `Todavía no hay alojamientos publicados en ${datos.pais}. Vuelve pronto.`,
      enlacePais: 'Ver todos los alojamientos de',
      totalAlojamientos: (datos) =>
        `${datos.total} ${datos.total === 1 ? 'alojamiento' : 'alojamientos'}`,
      cargando: 'Cargando destino...',
      noEncontrado: 'No encontramos este destino',
      noEncontradoAyuda:
        'Puede que el país no esté disponible todavía o que el enlace esté desactualizado.',
      verTodos: 'Ver todos los alojamientos',
      indiceTitulo: (datos) => `Alojamientos por país | ${datos.marca}`,
      indiceH1: 'Alojamientos por país',
      indiceResumen:
        'Elige un destino para ver los hoteles, hostales, cabañas, apartamentos y glamping ' +
        'disponibles en cada país.',
      indiceImagenAlt: (datos) => `Alojamientos por país en ${datos.marca}`,
      indiceMetadatos: (datos) =>
        `${datos.pais}. ${
          datos.paises
            ? `Consulta los alojamientos de ${datos.paises}.`
            : ''
        }`,
      indiceCargando: 'Cargando destinos...',
      indiceVacio: 'No hay destinos disponibles por ahora.',
      indiceConteo: (datos) => `${datos.total} ${datos.total === 1 ? 'alojamiento' : 'alojamientos'}`,
    },

    /**
     * Catálogo editorial de destinos turísticos (Salar de Uyuni, Cartagena...).
     *
     * No se mezcla con `pais`: un destino es un lugar concreto dentro de un
     * país, y sus páginas compiten por búsquedas del tipo "qué ver en X", no por
     * "alojamientos en X".
     */
    destinos: {
      indiceTitulo: (datos) => `Destinos turísticos | ${datos.marca}`,
      indiceH1: 'Destinos turísticos',
      indiceResumen:
        'Descubre los lugares que merecen el viaje y los alojamientos que tienes a mano para ' +
        'quedarte en cada uno.',
      indiceImagenAlt: (datos) => `Destinos turísticos en ${datos.marca}`,
      indiceMetadatos: (datos) =>
        `Destinos turísticos: ${datos.destinos}. Encuentra dónde dormir en cada uno.`,
      indiceCargando: 'Cargando destinos...',
      indiceVacio: 'No hay destinos disponibles por ahora.',
      buscarPlaceholder: 'Busca por nombre, ciudad o país...',
      buscarAria: 'Buscar destinos',
      limpiarBusqueda: 'Borrar búsqueda',
      filtrarPorPais: 'Filtrar por país',
      todosLosPaises: 'Todos los países',
      indiceResultados: (datos) =>
        `${datos.total} ${datos.total === 1 ? 'destino' : 'destinos'}`,
      indiceSinResultados: 'No encontramos destinos con esos criterios.',
      limpiarFiltros: 'Limpiar filtros',
      mostrando: (datos) =>
        `Mostrando ${datos.desde}–${datos.hasta} de ${datos.total} destinos`,
      paginacionAnterior: 'Anterior',
      paginacionSiguiente: 'Siguiente',
      verDestino: 'Descubrir destino',
      titulo: (datos) => `${datos.destino}, ${datos.pais} | ${datos.marca}`,
      h1: (datos) => `Qué ver y dónde alojarse en ${datos.destino}`,
      enPais: (datos) => `en ${datos.pais}`,
      tituloLista: (datos) => `Alojamientos en ${datos.destino}`,
      sinAlojamientos: (datos) =>
        `Todavía no hay alojamientos publicados en ${datos.destino}. Vuelve pronto.`,
      totalAlojamientos: (datos) =>
        `${datos.total} ${datos.total === 1 ? 'alojamiento' : 'alojamientos'}`,
      cargando: 'Cargando destino...',
      noEncontrado: 'No encontramos este destino',
      noEncontradoAyuda:
        'Puede que el destino no esté disponible todavía o que el enlace esté desactualizado.',
      volverAlIndice: 'Ver todos los destinos',
    },

    alojamiento: {
      titulo: (datos) => `${datos.nombre} en ${datos.ciudad} | ${datos.marca}`,
      enCiudad: (datos) => `en ${datos.ciudad}`,
      unidades: (datos) =>
        `${datos.unidades} ${datos.unidades === 1 ? 'opción de alojamiento' : 'opciones de alojamiento'} ` +
        `disponible${datos.unidades === 1 ? '' : 's'}.`,
      servicios: 'Servicios:',
      precioDesde: (datos) => `Precio desde ${datos.precio} por noche.`,
      tituloUnidades: 'Habitaciones y unidades disponibles',
      porNoche: (datos) =>
        `${datos.precio} por noche, hasta ${datos.huespedes} huéspedes.`,
      descripcionPrecio: (datos) => `Desde ${datos.precio} por noche.`,
      descripcionUnidades: (datos) =>
        `${datos.unidades} ${datos.unidades === 1 ? 'opción de alojamiento' : 'opciones de alojamiento'}`,
      cuentaCon: (datos) => `Cuenta con ${datos.amenities}.`,
      generico: 'Alojamiento',
      verUnidades: 'Ver Unidades',
      precioDesdeCorto: 'Precio desde',
      porNocheEtiqueta: '/ noche',
    },

    /** Textos del buscador y de los filtros de la portada. */
    buscador: {
destino: 'Nombre del alojamiento o dirección',
      placeholder: 'Buscar por nombre o dirección (ej. Gran Hotel)',
      populares: 'Todos los destinos',
      ciudadesDe: 'Ciudades del país elegido',
      eligePaisPrimero: 'Elige primero un país para ver sus ciudades.',
      limpiarCiudad: 'Quitar el filtro de ciudad',
      llegada: 'Llegada (Check-in)',
      salida: 'Salida (Check-out)',
      huespedes: 'Huéspedes',
      huespedUno: 'huésped',
      huespedesVarios: 'huéspedes',
      buscar: 'Buscar',
      limpiar: 'Limpiar filtros de búsqueda',
    },

    filtros: {
      titulo: 'Filtros Avanzados',
      restablecer: 'Restablecer',
      precioMaximo: 'Precio Máximo por Noche',
      tipoAlojamiento: 'Tipo de Alojamiento',
      tipoBano: 'Tipo de Baño',
      puntuacion: 'Puntuación Mínima',
      amenidades: 'Amenidades Populares',
      cualquiera: 'Cualquiera',
      todos: 'Todos',
      privado: 'Privado',
      compartido: 'Compartido',
      lugarEntero: '🏡 Lugar Entero (Cabañas/Aptos)',
      habitacionPrivada: '🏨 Habitación Privada (Hoteles)',
      literaCompartida: '🎒 Litera/Dormitorio Compartido (Hostales)',
      fantastic: 'Fantástico',
      excepcional: 'Excepcional',
      amenidadesLista: {
        wifi: 'WiFi rápido',
        kitchen: 'Cocina equipada',
        pool: 'Piscina / Alberca',
        parking: 'Estacionamiento gratis',
        pets: 'Admite mascotas',
        breakfast: 'Desayuno incluido',
        spa: 'Spa / Hidromasaje',
        ac: 'Aire acondicionado',
      },
    },

    categorias: {
      todos: 'Todos los tipos',
    },

    /** Insignias de amenidad de la tarjeta de alojamiento. */
    amenidadesTarjeta: {
      wifi: 'WiFi',
      pool: 'Piscina',
      kitchen: 'Cocina',
      parking: 'Parqueo',
      pets: 'Mascotas',
      breakfast: 'Desayuno',
      fireplace: 'Chimenea',
    },

    /**
     * Etiquetas de las amenidades, para la description y el JSON-LD.
     *
     * La base de datos guarda las amenidades como claves estables (`wifi`,
     * `pool`), no como texto, precisamente para poder traducirlas. Si se
     * guardara el literal, añadir un idioma obligaría a una migración de datos.
     */
    amenidades: {
      ac: 'Aire acondicionado',
      bar: 'Bar',
      bbq: 'Parrilla',
      beachfront: 'Frente al mar',
      breakfast: 'Desayuno incluido',
      coworking: 'Coworking',
      fireplace: 'Chimenea',
      garden: 'Jardín',
      gym: 'Gimnasio',
      heating: 'Calefacción',
      hottub: 'Jacuzzi',
      kitchen: 'Cocina',
      laundry: 'Lavandería',
      lockers: 'Casilleros',
      mountain_view: 'Vista a la montaña',
      parking: 'Parqueo',
      pets: 'Admite mascotas',
      pool: 'Piscina',
      restaurant: 'Restaurante',
      spa: 'Spa',
      wifi: 'WiFi',
      workspace: 'Espacio de trabajo',
    },

    paisesFiltro: {
      titulo: 'Destinos por país',
      todos: 'Todos los países',
    },

misReservas: {
      titulo: (datos) => `Consulta tu reserva | ${datos.marca}`,
      metadatos:
        'Introduce el código de reserva y el correo del huésped para ver los detalles, ' +
        'el desglose del precio o cancelar tu alojamiento.',
      h1: 'Gestiona tus reservas',
      resumen:
        'Introduce tu código de reserva y el correo del huésped para consultar los detalles de ' +
        'tu estancia, imprimir el comprobante o cancelar la reserva.',
    },

    /** Formulario para registrar un alojamiento. */
    empresas: {
      titulo: (datos) => `Registra tu alojamiento | ${datos.marca}`,
      metadatos:
        'Deja tu nombre, tu correo y una nota y registra tu alojamiento sin trámites ni esperas.',
      h1: 'Registra tu alojamiento',
      resumen:
        'Déjanos tu nombre, tu correo y un WhatsApp para poder contactarte. Revisaremos tu ' +
        'alojamiento y te escribiremos.',
      nombre: 'Nombre completo',
      nombreCompleto: 'Nombre completo',
      nombrePlaceholder: 'Ej. Ana Ramírez',
      correo: 'Correo electrónico',
      correoPlaceholder: 'Ej. ana@mi-hostel.com',
      pais: 'País',
      paisSelecciona: 'Selecciona tu país',
      errorPaises: 'No se pudieron cargar los pa\u00edses',
      paisesNoDisponibles: 'No se han podido cargar los países. Inténtalo de nuevo en un momento.',
      whatsapp: 'WhatsApp',
      whatsappPlaceholder: 'Ej. 600 123 456',
      whatsappAyuda: (datos) => `Número de ${datos.prefijo}. Puedes escribir el prefijo o no: lo quitamos por ti.`,
      detalle: 'Detalle',
      detallePlaceholder: 'Cuéntanos brevemente qué alojamiento tienes.',
      enviar: 'Enviar solicitud',
      enviando: 'Enviando...',
      enviada: 'Tu solicitud se ha enviado correctamente. Nos pondremos en contacto contigo.',
      errorCorreoDuplicado: 'Ya hay una solicitud registrada con ese correo.',
      errorGenerico: 'No se ha podido enviar la solicitud. Inténtalo de nuevo.',
      errorNombre: 'El nombre es obligatorio.',
      errorCorreo: 'Indica un correo válido.',
      errorPais: 'Selecciona tu país.',
      errorWhatsapp: (datos) =>
        `Indica un número de WhatsApp válido en ${datos.pais}. Ej. ${datos.ejemplo}`,
      errorDetalle: 'El detalle es demasiado largo (máximo 2000 caracteres).',
    },

    noEncontrado: {
      titulo: (datos) => `Página no encontrada | ${datos.marca}`,
      metadatos: 'La página que buscas no existe o ha cambiado de dirección.',
      h1: 'Página no encontrada',
      resumen:
        'La dirección que buscas no existe o ha cambiado. Prueba a buscar tu alojamiento desde el ' +
        'inicio.',
      irAExplorar: 'Ir a explorar alojamientos',
    },

    /** Texto del HTML de redirección de la raíz (fallback si no hay 301). */
    redireccion: {
      continuando: 'Continuando en',
    },

    /** Panel lateral de detalle y modal de reserva. */
    detalle: {
      servicios: 'Servicios',
      hastaHuespedes: (datos) => `Hasta ${datos.total} huéspedes`,
      porNocheEtiqueta: '/ noche',
      reservarUnidad: 'Reservar Unidad',
      banoPrivado: 'Baño Privado',
      banoCompartido: 'Baño Compartido',
      camas: 'Cama(s) disponible(s)',
      tipoFallback: 'Alojamiento',
      estrellas: (datos) => `${datos.total} Estrellas`,
      opiniones: (datos) => `(${datos.total} opiniones)`,
      acercaDe: 'Acerca del Alojamiento',
      horarios: 'Horarios',
      llegada: 'Llegada (Check-in):',
      salida: 'Salida (Check-out):',
      politicas: 'Políticas y Reglas',
      politicaPorDefecto: 'Cancelación estándar y respeto a horas de silencio.',
      habitacionesTitulo: 'Habitaciones y Unidades Disponibles',
      habitacionesSubtitulo: 'Selecciona la unidad que mejor se adapte a tu viaje',
      sinHabitaciones: 'No hay habitaciones registradas para esta propiedad.',
      mapa: {
        titulo: 'Ubicación',
        subtitulo: '¿Dónde está el alojamiento?',
        sinCoordenadas: 'Este alojamiento aún no tiene ubicación registrada.',
      },
      tipoLugarCompleto: 'Lugar Completo',
      tipoHabitacionPrivada: 'Habitación Privada',
      tipoDormitorio: 'Litera / Dormitorio Compartido',
      limpiezaUnica: (datos) => `+ ${datos.precio} limpieza (único)`,
      galeria: {
        abrir: 'Ver todas las fotos',
        ampliar: 'Ampliar foto',
        cerrar: 'Cerrar galería',
        anterior: 'Foto anterior',
        siguiente: 'Foto siguiente',
        verFoto: (datos) => `Ver foto ${datos.actual} de ${datos.total}`,
        contador: (datos) => `${datos.actual} / ${datos.total}`,
      },
    },

    /** Modal de reserva / confirmación. */
    reserva: {
      totalAPagar: 'Total a pagar',
      imprimirVoucher: 'Imprimir Voucher',
      verEnMisReservas: 'Ver en Mis Reservas',
datosHuesped: 'Datos del Huésped Principal',
      pais: 'País del teléfono',
      paisSelecciona: 'Selecciona tu país',
      errorPaises: 'No se pudieron cargar los pa\u00edses',
      paisesNoDisponibles: 'No pudimos cargar los países. Recarga la página.',
      paisAyuda: (datos) => `Se guardará con tu reserva el prefijo ${datos.prefijo}.`,
      telefono: 'Teléfono / WhatsApp',
      telefonoPlaceholder: 'Ej. 71234567',
      correo: 'Correo Electrónico',
      camposObligatorios:
        'Por favor completa los campos obligatorios (Nombre, Email, País y Teléfono).',
      errorPais: 'Selecciona un país válido de la lista.',
      errorTelefono: (datos) =>
        `Ese número no es válido para ${datos.pais}. Ej.: ${datos.ejemplo}`,
      errorReserva: 'No se pudo procesar la reserva. Por favor verifica los datos.',
      nombrePlaceholder: 'Ej. María Fernanda Gómez',
      correoPlaceholder: 'tucorreo@ejemplo.com',
      notasPlaceholder: 'Hora estimada de llegada, cama extra, etc.',
      tarifaLimpieza: 'Tarifa de limpieza',
      impuestos: 'Impuestos y tasas de servicio (10%)',
      totalFinal: 'Total Final a Pagar',
      confirmando: 'Confirmando Reserva...',
      confirmarAhora: 'Confirmar Reserva Ahora',
      notaPago:
        'Confirmación inmediata sin cobro por adelantado. Pagas al registrarte en la propiedad.',
      errorTarifa: 'Error al calcular la tarifa para las fechas seleccionadas.',
      confirmada: '¡Reserva Confirmada con Éxito!',
      gracias: (datos) => `Gracias, ${datos.nombre}`,
      enviadaA: (datos) => `Hemos enviado la confirmación detallada a ${datos.correo}.`,
      codigoReserva: 'C\u00f3digo de Reserva',
      paquete: 'Paquete',
      copiado: 'Copiado',
      copiarCodigo: 'Copiar Código',
      alojamiento: 'Alojamiento',
      unidad: 'Unidad',
      fechas: 'Fechas',
      duracion: 'Duración',
      noches: (datos) => `(${datos.total} noches)`,
    },

    /** Listado y gestión de reservas del huésped. */
    reservas: {
      h1: 'Gestiona Tus Reservas',
      resumen:
        'Consulta los detalles de tu estancia, imprime tu comprobante o cancela tu reserva ' +
        'ingresando tu código de referencia y correo.',
      codigoPlaceholder: 'Ej. BK-A1B2C3D',
      correoPlaceholder: 'correo@ejemplo.com',
      imprimir: 'Imprimir',
      cancelar: 'Cancelar Esta Reserva',
      confirmarCancelacion: 'Confirmar Cancelación',
      cancelando: 'Cancelando...',
      tituloCancelar: '¿Deseas cancelar esta reserva?',
      motivoPlaceholder: 'Ej. Cambio imprevisto de fecha de viaje',
      totalAPagar: 'Total a pagar',
      cancelacionCorrecta: 'Tu reserva ha sido cancelada correctamente.',
      errorCancelar: 'Error al cancelar la reserva.',
    },
  },

};

/**
 * Textos de la interfaz, en español.
 *
 * La plataforma es monolingüe, así que `copy()` devuelve siempre este bloque.
 * Se mantiene el parámetro por compatibilidad con los componentes que aún pasan
 * el idioma, pero se ignora.
 */
export function copy(_idioma) {
  return COPYS.es;
}

/** Códigos de idioma con textos propios. Solo hay uno. */
export function idiomasConCopy() {
  return Object.keys(COPYS);
}

/**
 * Etiqueta de una amenidad ya traducida.
 *
 * La base de datos guarda las amenidades como claves (`wifi`, `pool`) y no como
 * texto. Esta función es el único punto donde esa clave se convierte en texto.
 */
export function etiquetaAmenidadDe(slug) {
  if (!slug) return '';

  const texto = COPYS.es.amenidades[slug];

  if (texto) return texto;

  // Slug sin traducción: se humaniza para no dejar la clave cruda en la página.
  return slug.replace(/_/g, ' ').replace(/\b\w/g, (c) => c.toUpperCase());
}

/** Idiomas que se publican. Solo hay uno. */
export function idiomasPublicables() {
  return [IDIOMA_POR_DEFECTO];
}

/**
 * Idiomas del sitio. El registro de metadatos y el prerender recorren esta
 * lista; con un único idioma generan una sola variante de cada ruta.
 */
export const IDIOMAS_PUBLICABLES = idiomasPublicables();

/** No hay idiomas que comparar: la paridad siempre está resuelta. */
export function problemaDeParidad() {
  return null;
}
