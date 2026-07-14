export default function CareersRoleFilters({ categories, activeCategory, onChange }) {
  return (
    <div className="mt-12 flex flex-wrap justify-center gap-4">
      {categories.map((category) => (
        <button
          key={category}
          type="button"
          onClick={() => onChange(category)}
          className={`rounded-full px-6 py-3 text-base font-medium transition-all duration-300 ${
            activeCategory === category
              ? 'bg-blue-600 text-white shadow-[0_16px_32px_rgba(37,99,235,0.28)]'
              : 'bg-slate-100 text-slate-700 hover:bg-slate-200'
          }`}
        >
          {category}
        </button>
      ))}
    </div>
  )
}