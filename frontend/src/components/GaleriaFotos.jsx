import React, { useCallback, useEffect, useState } from 'react';
import { MdChevronLeft, MdChevronRight, MdFullscreen, MdClose } from 'react-icons/md';

/**
 * Galería de fotos de un alojamiento.
 *
 * En escritorio muestra un mosaico (una foto grande y hasta cuatro pequeñas) y
 * en móvil una foto principal con flechas más una tira de miniaturas. En ambos
 * casos se puede abrir un visor a pantalla completa con teclado (Esc, flechas).
 */
export default function GaleriaFotos({ images, alt, textos, acento = 'border-rose-500' }) {
  const [abierto, setAbierto] = useState(false);
  const [indice, setIndice] = useState(0);

  const total = images.length;

  const mover = useCallback(
    (delta) => setIndice((i) => (i + delta + total) % total),
    [total],
  );

  const abrir = useCallback((i) => {
    setIndice(i);
    setAbierto(true);
  }, []);

  const cerrar = useCallback(() => setAbierto(false), []);

  useEffect(() => {
    if (!abierto) return undefined;

    const alTeclear = (e) => {
      if (e.key === 'Escape') cerrar();
      else if (e.key === 'ArrowLeft') mover(-1);
      else if (e.key === 'ArrowRight') mover(1);
    };

    window.addEventListener('keydown', alTeclear);
    const overflow = document.body.style.overflow;
    document.body.style.overflow = 'hidden';

    return () => {
      window.removeEventListener('keydown', alTeclear);
      document.body.style.overflow = overflow;
    };
  }, [abierto, cerrar, mover]);

  if (!total) return null;

  const visibles = images.slice(0, 5);
  const mosaico = total >= 4;

  return (
    <>
      {mosaico ? (
        <div className="relative hidden sm:block">
          <div className="grid grid-cols-4 grid-rows-2 gap-1 h-[380px] lg:h-[460px]">
            <BotonFoto
              imagen={images[0]}
              alt={alt}
              className="col-span-2 row-span-2"
              onClick={() => abrir(0)}
            />
            {visibles.slice(1).map((img, i) => (
              <BotonFoto
                key={i}
                imagen={img}
                onClick={() => abrir(i + 1)}
                contador={i === 3 && total > 5 ? `+${total - 5}` : null}
              />
            ))}
          </div>

          <button
            type="button"
            onClick={() => abrir(0)}
            className="absolute bottom-4 right-4 flex items-center gap-1.5 px-3 py-2 bg-white/95 hover:bg-white text-slate-900 text-xs font-bold rounded-lg shadow-lg transition-colors cursor-pointer"
          >
            <MdFullscreen className="w-3.5 h-3.5" />
            {textos.abrir}
          </button>
        </div>
      ) : (
        <div
          className={`hidden sm:grid gap-1 h-[380px] lg:h-[460px] ${
            total === 1 ? 'grid-cols-1' : 'grid-cols-2'
          }`}
        >
          {images.map((img, i) => (
            <BotonFoto
              key={i}
              imagen={img}
              alt={i === 0 ? alt : ''}
              onClick={() => abrir(i)}
            />
          ))}
        </div>
      )}

      {/* Móvil: foto principal + flechas + miniaturas */}
      <div className="sm:hidden">
        <div className="relative aspect-4/3 w-full bg-slate-900">
          <img
            src={images[indice]}
            alt={alt}
            className="w-full h-full object-cover"
          />

          <span className="absolute top-3 right-3 px-2.5 py-1 bg-black/60 backdrop-blur-sm text-white text-xs font-bold rounded-full">
            {textos.contador({ actual: indice + 1, total })}
          </span>

          {total > 1 && (
            <>
              <button
                type="button"
                onClick={() => mover(-1)}
                aria-label={textos.anterior}
                className="absolute left-2 top-1/2 -translate-y-1/2 p-2 bg-black/50 hover:bg-black/70 text-white rounded-full transition-colors cursor-pointer"
              >
                <MdChevronLeft className="w-5 h-5" />
              </button>
              <button
                type="button"
                onClick={() => mover(1)}
                aria-label={textos.siguiente}
                className="absolute right-2 top-1/2 -translate-y-1/2 p-2 bg-black/50 hover:bg-black/70 text-white rounded-full transition-colors cursor-pointer"
              >
                <MdChevronRight className="w-5 h-5" />
              </button>
            </>
          )}

          <button
            type="button"
            onClick={() => abrir(indice)}
            aria-label={textos.ampliar}
            className="absolute bottom-3 right-3 flex items-center gap-1.5 px-3 py-2 bg-white/95 text-slate-900 text-xs font-bold rounded-lg shadow-lg cursor-pointer"
          >
            <MdFullscreen className="w-3.5 h-3.5" />
            {textos.ampliar}
          </button>
        </div>

        {total > 1 && (
          <div className="flex gap-2 overflow-x-auto p-2 bg-slate-900">
            {images.map((img, i) => (
              <button
                key={i}
                type="button"
                onClick={() => setIndice(i)}
                aria-label={textos.verFoto({ actual: i + 1, total })}
                className={`shrink-0 w-20 h-14 rounded-lg overflow-hidden border-2 transition-all ${
                  indice === i
                    ? acento
                    : 'border-transparent opacity-60 hover:opacity-100'
                }`}
              >
                <img src={img} alt="" className="w-full h-full object-cover" />
              </button>
            ))}
          </div>
        )}
      </div>

      {/* Visor a pantalla completa */}
      {abierto && (
        <div
          role="dialog"
          aria-modal="true"
          aria-label={alt}
          onClick={cerrar}
          className="fixed inset-0 z-[70] bg-black/90 backdrop-blur-sm flex flex-col"
        >
          <div className="flex items-center justify-between px-4 py-3 text-white">
            <span className="text-sm font-semibold tabular-nums">
              {textos.contador({ actual: indice + 1, total })}
            </span>
            <button
              type="button"
              onClick={cerrar}
              aria-label={textos.cerrar}
              className="p-2 hover:bg-white/10 rounded-full transition-colors cursor-pointer"
            >
              <MdClose className="w-6 h-6" />
            </button>
          </div>

          <div
            className="relative flex-1 min-h-0 flex items-center justify-center px-2 sm:px-16"
            onClick={(e) => e.stopPropagation()}
          >
            {total > 1 && (
              <>
                <button
                  type="button"
                  onClick={() => mover(-1)}
                  aria-label={textos.anterior}
                  className="absolute left-2 sm:left-4 p-3 bg-white/10 hover:bg-white/20 text-white rounded-full transition-colors cursor-pointer"
                >
                  <MdChevronLeft className="w-6 h-6" />
                </button>
                <button
                  type="button"
                  onClick={() => mover(1)}
                  aria-label={textos.siguiente}
                  className="absolute right-2 sm:right-4 p-3 bg-white/10 hover:bg-white/20 text-white rounded-full transition-colors cursor-pointer"
                >
                  <MdChevronRight className="w-6 h-6" />
                </button>
              </>
            )}

            <img
              src={images[indice]}
              alt={alt}
              className="max-h-full max-w-full object-contain rounded-lg"
            />
          </div>

          {total > 1 && (
            <div
              className="flex gap-2 overflow-x-auto px-4 pb-4 pt-2 sm:justify-center"
              onClick={(e) => e.stopPropagation()}
            >
              {images.map((img, i) => (
                <button
                  key={i}
                  type="button"
                  onClick={() => setIndice(i)}
                  aria-label={textos.verFoto({ actual: i + 1, total })}
                  className={`shrink-0 w-16 h-11 rounded-md overflow-hidden border-2 transition-all ${
                    indice === i
                      ? acento
                      : 'border-transparent opacity-60 hover:opacity-100'
                  }`}
                >
                  <img src={img} alt="" className="w-full h-full object-cover" />
                </button>
              ))}
            </div>
          )}
        </div>
      )}
    </>
  );
}

/** Celda del mosaico de escritorio. */
function BotonFoto({ imagen, alt = '', className = '', contador = null, onClick }) {
  return (
    <button
      type="button"
      onClick={onClick}
      className={`group relative overflow-hidden bg-slate-100 cursor-pointer ${className}`}
    >
      <img
        src={imagen}
        alt={alt}
        className="w-full h-full object-cover transition-transform duration-300 group-hover:scale-105"
      />

      {contador && (
        <span className="absolute inset-0 flex items-center justify-center bg-black/50 text-white text-lg font-bold">
          {contador}
        </span>
      )}
    </button>
  );
}