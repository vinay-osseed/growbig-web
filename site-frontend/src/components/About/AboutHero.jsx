export default function AboutHero() {
  return (
    <section className="relative overflow-hidden bg-[#071a3e] px-6 py-24 text-white lg:py-32 lg:px-8">
      <div className="absolute inset-0 bg-[linear-gradient(rgba(255,255,255,0.04)_1px,transparent_1px),linear-gradient(90deg,rgba(255,255,255,0.04)_1px,transparent_1px)] bg-[size:56px_56px] opacity-60" />
      <div className="absolute inset-0 bg-[radial-gradient(ellipse_at_30%_50%,rgba(249,115,22,0.12),transparent_50%),radial-gradient(ellipse_at_80%_20%,rgba(59,130,246,0.18),transparent_45%)]" />

      <div className="relative mx-auto max-w-4xl text-center">
        <div className="mb-8 inline-flex items-center gap-2 rounded-full border border-white/10 bg-black/30 px-5 py-2 text-sm font-medium text-slate-300 backdrop-blur-sm">
          <span className="h-2 w-2 rounded-full bg-green-400" />
          Company Overview and value
        </div>

        <h1 className="text-5xl font-black tracking-[-0.04em] text-white sm:text-6xl lg:text-7xl">
          We Are <span className="text-blue-400">Grow</span>
          <span className="text-amber-400">Big</span>
        </h1>

        <p className="mx-auto mt-6 max-w-2xl text-lg leading-8 text-slate-300">
          We help businesses grow through innovative software development, AI solutions, digital marketing, and business process outsourcing—delivering reliable, scalable, and future-ready technology solutions.
        </p>

        {/*<div className="mt-12 grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
          {[
            { num: '150+', label: 'Projects' },
            { num: '50+', label: 'Clients' },
            { num: '30+', label: 'Team Members' },
            { num: '6+', label: 'Years' },
          ].map((stat) => (
            <div key={stat.label} className="rounded-2xl border border-white/10 bg-white/5 p-6 text-center backdrop-blur-sm">
              <span className="block text-3xl font-extrabold text-amber-400">{stat.num}</span>
              <span className="mt-2 block text-sm text-slate-400">{stat.label}</span>
            </div>
          ))}
        </div>*/}
      </div>
    </section>
  )
}

