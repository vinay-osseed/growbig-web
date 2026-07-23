export default function CareersRoleCard({ position }) {
  return (
    <article className="rounded-[1.75rem] border border-slate-200 bg-white p-6 shadow-[0_10px_30px_rgba(15,23,42,0.05)] transition-all duration-300 hover:-translate-y-0.5 hover:shadow-[0_18px_40px_rgba(15,23,42,0.08)] sm:p-8">
      <div className="flex flex-col gap-6 lg:flex-row lg:items-center lg:justify-between">
        <div>
          <div className="flex flex-wrap items-center gap-3">
            <span className="rounded-full bg-blue-50 px-4 py-2 text-sm font-semibold text-blue-700">
              {position.department}
            </span>
            <span className="rounded-full bg-slate-100 px-4 py-2 text-sm font-medium text-slate-700">
              {position.type}
            </span>
            <span className="rounded-full bg-emerald-50 px-4 py-2 text-sm font-medium text-emerald-700">
              {position.badge}
            </span>
          </div>

          <h3 className="mt-5 text-l font-bold tracking-[-0.03em] text-slate-950 sm:text-2xl">
           {position.title}
          </h3>

          <div className="mt-5 flex flex-wrap items-center gap-x-6 gap-y-3 text-sm text-slate-600">
            <span className="inline-flex items-center gap-2">
              <svg width="16" height="16" fill="none" stroke="currentColor" strokeWidth="1.9" viewBox="0 0 24 24">
                <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0118 0z" />
                <circle cx="12" cy="10" r="3" />
              </svg>
              {position.location}
            </span>
            <span className="inline-flex items-center gap-2">
              <svg width="16" height="16" fill="none" stroke="currentColor" strokeWidth="1.9" viewBox="0 0 24 24">
                <circle cx="12" cy="12" r="9" />
                <path d="M12 7v6l4 2" />
              </svg>
              {position.posted}
            </span>
            <span className="inline-flex items-center gap-2">
              <svg width="16" height="16" fill="none" stroke="currentColor" strokeWidth="1.9" viewBox="0 0 24 24">
                <rect x="3" y="4" width="18" height="16" rx="2" />
                <path d="M8 20v-3a4 4 0 014-4h0a4 4 0 014 4v3" />
              </svg>
              {position.experience}
            </span>
          </div>
        </div>

        <button className="inline-flex items-center justify-center gap-3 rounded-full bg-blue-600 px-7 py-4 text-base font-semibold text-white shadow-[0_18px_35px_rgba(37,99,235,0.24)] transition-all duration-300 hover:-translate-y-0.5 hover:bg-blue-700 hover:shadow-[0_22px_42px_rgba(37,99,235,0.3)]">
          Apply Now
          <svg width="18" height="18" fill="none" stroke="currentColor" strokeWidth="2.3" viewBox="0 0 24 24">
            <line x1="5" y1="12" x2="19" y2="12" />
            <polyline points="12 5 19 12 12 19" />
          </svg>
        </button>
      </div>
    </article>
  )
}