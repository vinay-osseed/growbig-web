export default function AboutCtaSection() {
  return (
    <section className="relative overflow-hidden bg-[#0e1526] px-6 py-16 text-white lg:px-8 lg:py-20">
  <div className="absolute inset-0 bg-[radial-gradient(ellipse_at_18%_50%,rgba(30,64,175,0.42),transparent_45%),radial-gradient(ellipse_at_85%_20%,rgba(249,115,22,0.18),transparent_45%)]" />

  <div className="relative mx-auto max-w-4xl text-center">
    <h2 className="text-3xl font-extrabold tracking-tight text-white sm:text-4xl lg:text-[3rem]">
       Want to join our growing team?
    </h2>

    <p className="mx-auto mt-4 max-w-3xl text-lg leading-8 text-slate-300">
      We are always looking for talented people who share our passion for great software.
    </p>
         <div className="mt-12 flex justify-center">
          <a
            href="/careers"
            className="inline-flex items-center gap-3 rounded-[1.5rem] bg-orange-500 px-8 py-4 text-lg font-bold text-white shadow-[0_18px_40px_rgba(249,115,22,0.35)] transition-transform duration-300 hover:-translate-y-0.5 hover:bg-orange-400"
          >
            View Open Roles
            <svg width="18" height="18" fill="none" stroke="currentColor" strokeWidth="2.4" viewBox="0 0 24 24">
              <line x1="5" y1="12" x2="19" y2="12" />
              <polyline points="12 5 19 12 12 19" />
            </svg>
          </a>
        </div>
  </div>
</section>
  )
}

