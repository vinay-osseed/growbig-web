const values = [
  {
    title: 'Innovation First',
    desc: 'We push boundaries and embrace emerging technologies to deliver forward-thinking solutions.',
    icon: (
      <svg width="24" height="24" fill="none" stroke="currentColor" strokeWidth="2" viewBox="0 0 24 24">
        <path d="M13 10V3L4 14h7v7l9-11h-7z" />
      </svg>
    ),
    badge: 'bg-orange-50 text-orange-500',
  },
  {
    title: 'Client-Centric',
    desc: 'Every decision we make is driven by the outcomes and satisfaction of our clients.',
    icon: (
      <svg width="24" height="24" fill="none" stroke="currentColor" strokeWidth="2" viewBox="0 0 24 24">
        <path d="M12 21s-7-4.35-7-10.2A4.8 4.8 0 0 1 14 7.4a4.8 4.8 0 0 1 9 3.4C23 16.65 16 21 16 21z" />
      </svg>
    ),
    badge: 'bg-rose-50 text-rose-500',
  },

  {
    title: 'Continuous Growth',
    desc: 'Learning never stops. We invest in people, process, and tools to stay ahead of the curve.',
    icon: (
      <svg width="24" height="24" fill="none" stroke="currentColor" strokeWidth="2" viewBox="0 0 24 24">
        <path d="M12 20V10" />
        <path d="M18 20V6" />
        <path d="M6 20v-4" />
      </svg>
    ),
    badge: 'bg-amber-50 text-amber-500',
  },
]

export default function AboutValuesSection() {
  return (
    <section className="bg-white py-20 sm:py-24">
      <div className="mx-auto max-w-7xl px-6 lg:px-8">
        <div className="text-center">
          <div className="mb-4 inline-flex items-center rounded-full border border-orange-200 bg-orange-50 px-4 py-1.5 text-sm font-semibold uppercase tracking-[0.2em] text-orange-500">
            What We Stand For
          </div>
          <h2 className="text-4xl font-black tracking-[-0.04em] text-slate-900 sm:text-5xl">Our Core Values</h2>
          <p className="mx-auto mt-4 max-w-2xl text-base leading-7 text-slate-600">
            Six principles that shape how we think, work, and deliver for every client.
          </p>
        </div>
        <div className="mt-12 grid gap-6 md:grid-cols-2 xl:grid-cols-3">
  {values.map((v) => (
    <article
      key={v.title}
      className="rounded-[1.75rem] border border-slate-200 bg-white p-7 shadow-[0_20px_50px_rgba(15,23,42,0.05)] transition-transform duration-300 hover:-translate-y-1 hover:shadow-lg"
    >
      <div
        className={`flex h-14 w-14 items-center justify-center rounded-2xl ${v.badge}`}
      >
        {v.icon}
      </div>

      <h3 className="mt-5 text-xl font-bold tracking-[-0.02em] text-slate-900">
        {v.title}
      </h3>

      <p className="mt-3 text-sm leading-7 text-slate-600">
        {v.desc}
      </p>
    </article>
  ))}
</div>
</div>
    </section>
  )
}