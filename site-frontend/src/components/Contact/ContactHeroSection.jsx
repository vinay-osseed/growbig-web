const heroStats = [
  {
    icon: (
      <svg width="20" height="20" fill="none" stroke="currentColor" strokeWidth="2" viewBox="0 0 24 24">
        <path d="M22 16.92v3a2 2 0 01-2.18 2 19.79 19.79 0 01-8.63-3.07 19.5 19.5 0 01-6-6 19.79 19.79 0 01-3.07-8.67A2 2 0 014.11 2h3a2 2 0 012 1.72c.127.96.361 1.903.7 2.81a2 2 0 01-.45 2.11L8.09 9.91a16 16 0 006 6l1.27-1.27a2 2 0 012.11-.45c.907.339 1.85.573 2.81.7A2 2 0 0122 16.92z" />
      </svg>
    ),
    label: 'Call Us',
    value: '+91 9146802212',
    link: 'tel:+919146802212',
  },
  {
    icon: (
      <svg width="20" height="20" fill="none" stroke="currentColor" strokeWidth="2" viewBox="0 0 24 24">
        <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z" />
        <polyline points="22,6 12,13 2,6" />
      </svg>
    ),
    label: 'Email Us',
    value: 'office@growbigllp.com',
    link: 'mailto:office@growbigllp.com',
  },
  {
    icon: (
      <svg width="20" height="20" fill="none" stroke="currentColor" strokeWidth="2" viewBox="0 0 24 24">
        <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0118 0z" />
        <circle cx="12" cy="10" r="3" />
      </svg>
    ),
    label: 'Visit Us',
    value: 'Sawantwadi, Maharashtra\nNarayan Arcade, Near ST Stand',
    link: null,
  },
]

export default function ContactHeroSection() {
  return (
    <section className="relative overflow-hidden bg-[#0f1d3b] px-6 pb-32 pt-20 text-white lg:px-8 lg:pb-36 lg:pt-24">
      <div className="absolute inset-0 bg-[linear-gradient(rgba(59,130,246,0.08)_1px,transparent_1px),linear-gradient(90deg,rgba(59,130,246,0.08)_1px,transparent_1px)] bg-[size:64px_64px] opacity-35" />
      <div className="absolute inset-0 bg-[radial-gradient(circle_at_50%_28%,rgba(37,99,235,0.28),transparent_32%),radial-gradient(circle_at_88%_18%,rgba(249,115,22,0.15),transparent_22%)]" />

      <div className="relative mx-auto max-w-4xl text-center">
        <div className="inline-flex items-center gap-2 rounded-full border border-amber-500/30 bg-amber-500/10 px-4 py-2 text-sm font-semibold text-amber-300 shadow-[0_0_0_1px_rgba(245,158,11,0.08)]">
          <span className="h-2 w-2 rounded-full bg-emerald-400" />
          We typically reply within few hours
        </div>

        <h1 className="mt-8 text-5xl font-black tracking-[-0.05em] text-white sm:text-6xl lg:text-[4.6rem] lg:leading-[0.98]">
          Let's Start a
          <span className="block bg-[linear-gradient(90deg,#2563eb_0%,#3b82f6_42%,#f59e0b_100%)] bg-clip-text text-transparent">
            Conversation
          </span>
        </h1>

        <p className="mx-auto mt-6 max-w-2xl text-lg leading-8 text-slate-300 sm:text-xl">
          Tell us about your project, your goals, or simply say hello. We'd love to hear from you.
        </p>
      </div>

      <div className="relative z-10 mx-auto mt-16 grid max-w-7xl gap-5 lg:-mb-20 lg:grid-cols-3 lg:gap-6">
        {heroStats.map((item) => (
          <div key={item.label} className="rounded-[2rem] border border-slate-200 bg-white px-8 py-10 text-center text-slate-900 shadow-[0_24px_60px_rgba(15,23,42,0.12)]">
            <div className="mx-auto flex h-14 w-14 items-center justify-center rounded-2xl bg-slate-100 text-blue-600">
              {item.icon}
            </div>
            <h3 className="mt-5 text-lg font-bold text-slate-950">{item.label}</h3>
            {item.link ? (
              <a href={item.link} className="mt-2 block text-sm font-semibold text-blue-600 transition hover:text-blue-700">
                {item.value}
              </a>
            ) : (
              <p className="mt-2 whitespace-pre-line text-sm text-slate-500">{item.value}</p>
            )}
          </div>
        ))}
      </div>
    </section>
  )
}