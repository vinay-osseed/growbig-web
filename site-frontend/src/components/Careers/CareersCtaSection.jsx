export default function CareersCtaSection() {
  return (
    <section className="relative overflow-hidden bg-[#111827] px-6 py-16 text-white sm:py-20 lg:px-8">
      <div className="absolute inset-0 bg-[radial-gradient(circle_at_14%_30%,rgba(37,99,235,0.24),transparent_28%),radial-gradient(circle_at_76%_40%,rgba(249,115,22,0.08),transparent_26%)]" />
      <div className="relative mx-auto max-w-5xl text-center">
        <h2 className="text-3xl font-black tracking-[-0.05em] text-white sm:text-5xl lg:text-4xl">
          Don&apos;t see the right role?
        </h2>

        <p className="mx-auto mt-4 max-w-2xl text-base leading-7 text-slate-300 sm:text-lg">
          Send us your resume anyway. We&apos;re always interested in talented people who want to build great things.
        </p>

        <a
          href="mailto:office@growbigllp.com"
          className="mt-8 inline-flex items-center gap-3 rounded-[1.5rem] bg-[#f08a00] px-8 py-4 text-base font-semibold text-white shadow-[0_22px_44px_rgba(240,138,0,0.38)] transition-all duration-300 hover:-translate-y-0.5 hover:bg-[#ff9800]"
        >
          <svg width="20" height="20" fill="none" stroke="currentColor" strokeWidth="2" viewBox="0 0 24 24">
            <rect x="3" y="5" width="18" height="14" rx="2" />
            <path d="M3 7l9 6 9-6" />
          </svg>

          office@growbigllp.com
        </a>
      </div>
    </section>
  )
}