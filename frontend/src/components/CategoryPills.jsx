import React from 'react';
import { MdBusiness, MdHome, MdApartment, MdLandscape, MdGridOn, MdTune } from 'react-icons/md';
import { useIdioma } from '../hooks/useIdioma';
import { copy } from '../seo/copys';

const iconMap = {
  Building2: MdBusiness,
  BedSingle: MdHome,
  Home: MdHome,
  Building: MdApartment,
  Tent: MdLandscape,
};

export default function CategoryPills({ types, selectedSlug, onSelectCategory }) {
  const idioma = useIdioma();
  const textos = copy(idioma);

  return (
    <div className="w-full border-b border-slate-200 bg-white/80 backdrop-blur-xs py-4">
      <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div className="flex items-center gap-3 overflow-x-auto pb-1 scrollbar-none">
          
          {/* Option: Todos */}
          <button
            onClick={() => onSelectCategory('all')}
            className={`flex items-center gap-2.5 px-4 py-2.5 rounded-2xl text-sm font-semibold whitespace-nowrap transition-all duration-200 shrink-0 ${
              selectedSlug === 'all'
                    ? 'bg-gradient-to-b from-slate-300 to-slate-500 text-white shadow-md shadow-slate-400/30 scale-102'
                : 'bg-slate-100 text-slate-600 hover:bg-slate-200 hover:text-slate-900'
            }`}
          >
            <MdGridOn className="w-4 h-4" />
            <span>{textos.categorias.todos}</span>
          </button>

          {/* Dynamic categories from backend */}
          {types.map((type) => {
            const IconComponent = iconMap[type.icon] || MdTune;
            const isSelected = selectedSlug === type.slug;

            return (
              <button
                key={type.id}
                onClick={() => onSelectCategory(type.slug)}
                className={`flex items-center gap-2.5 px-4 py-2.5 rounded-2xl text-sm font-semibold whitespace-nowrap transition-all duration-200 shrink-0 ${
                  isSelected
                ? 'bg-gradient-to-b from-slate-300 to-slate-500 text-white shadow-md shadow-slate-400/30 scale-102'
                    : 'bg-slate-100 text-slate-700 hover:bg-slate-200 hover:text-slate-900'
                }`}
              >
                <IconComponent className="w-4 h-4" />
                <span>{type.name}</span>
                {type.accommodations_count > 0 && (
                  <span className={`px-2 py-0.5 rounded-full text-xs font-bold ${
                    isSelected ? 'bg-white/20 text-white' : 'bg-slate-200/80 text-slate-600'
                  }`}>
                    {type.accommodations_count}
                  </span>
                )}
              </button>
            );
          })}

        </div>
      </div>
    </div>
  );
}
