export default function ContactInfoPanel() {
  return (
    <div className="space-y-6">
      <div className="overflow-hidden rounded-[2rem] border border-slate-200 bg-[#0f172a] p-6 shadow-[0_18px_50px_rgba(15,23,42,0.18)] sm:p-8">
        <div className="flex min-h-[260px] items-center justify-center rounded-[1.6rem] border border-white/10 bg-[linear-gradient(180deg,rgba(15,23,42,0.95),rgba(30,41,59,0.95))] p-6 text-center text-white">
          <div>
            <div className="mx-auto flex h-16 w-16 items-center justify-center rounded-full border border-amber-500/60 bg-amber-500/10 text-amber-400">
              <svg width="28" height="28" fill="none" stroke="currentColor" strokeWidth="2.2" viewBox="0 0 24 24">
                <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0118 0z" />
                <circle cx="12" cy="10" r="3" />
              </svg>
            </div>
            <h3 className="mt-6 text-xl font-bold text-white">GrowBig Technologies LLP</h3>
            <p className="mt-2 text-sm leading-7 text-slate-300">
              Narayan Arcade, 1st Floor Above Axis Bank
              <br />
              Near ST Stand, Sawantwadi
            </p>
          </div>
        </div>
      </div>

      <div className="rounded-[2rem] border border-slate-200 bg-white p-6 shadow-[0_18px_50px_rgba(15,23,42,0.08)] sm:p-8">
        <h3 className="text-2xl font-bold tracking-[-0.03em] text-slate-950">Follow Us</h3>
        <div className="mt-6 grid gap-4 sm:grid-cols-2">
          {[
            {
              label: 'LinkedIn',
              href: 'https://www.linkedin.com/company/growbig-technologies/',
              icon: (
                <svg width="18" height="18" fill="none" stroke="currentColor" strokeWidth="2" viewBox="0 0 24 24">
                  <path d="M16 8a6 6 0 016 6v6h-4v-6a2 2 0 00-4 0v6h-4v-12h4v2" />
                  <rect x="2" y="9" width="4" height="12" />
                  <circle cx="4" cy="4" r="2" />
                </svg>
              ),
            },
            {
              label: 'Instagram',
              href: 'https://www.instagram.com/growbigllp/',
              icon: (
                <svg width="18" height="18" fill="none" stroke="currentColor" strokeWidth="2" viewBox="0 0 24 24">
                  <rect x="3" y="3" width="18" height="18" rx="5" />
                  <circle cx="12" cy="12" r="4" />
                  <circle cx="17.5" cy="6.5" r="1" fill="currentColor" stroke="none" />
                </svg>
              ),
            },
            {
              label: 'Facebook',
              href: 'https://www.facebook.com/share/182RC2vgWz/',
              icon: (
                <svg width="18" height="18" fill="none" stroke="currentColor" strokeWidth="2" viewBox="0 0 24 24">
                  <path d="M18 2h-3a5 5 0 00-5 5v3H7v4h3v8h4v-8h3l1-4h-4V7a1 1 0 011-1h3z" />
                </svg>
              ),
            },
            /*{
              label: 'GitHub',
              href: '#',
              icon: (
                <svg width="18" height="18" fill="none" stroke="currentColor" strokeWidth="2" viewBox="0 0 24 24">
                  <path d="M9 19c-5 1.5-5-2.5-7-3" />
                  <path d="M15 22v-3.87a3.37 3.37 0 00-.94-2.61c3.14-.35 6.44-1.54 6.44-7A5.44 5.44 0 0018 4.77 5.07 5.07 0 0017.91 1S16.73.65 15 2.48a13.38 13.38 0 00-6 0C7.27.65 6.09 1 6.09 1A5.07 5.07 0 006 4.77a5.44 5.44 0 00-1.5 3.75c0 5.42 3.3 6.63 6.44 7A3.37 3.37 0 0010 18.13V22" />
                </svg>
              ),
            },*/
          ].map((item) => (
            <a
              key={item.label}
              href={item.href}
               target="_blank"
               rel="noopener noreferrer"
              className="flex items-center gap-3 rounded-2xl border border-slate-200 bg-slate-50 px-4 py-4 text-slate-700 transition hover:border-blue-200 hover:bg-white hover:text-slate-950"
            >
              <span className="flex h-9 w-9 items-center justify-center rounded-full bg-white text-slate-600 shadow-sm">
                {item.icon}
              </span>
              <span className="text-sm font-medium">{item.label}</span>
            </a>
          ))}
        </div>
      </div>
    </div>
  )
}