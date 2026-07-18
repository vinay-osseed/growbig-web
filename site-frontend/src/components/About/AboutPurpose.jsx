export default function MissionVisionSection() {
  return (
    <section className="bg-[#F7F9FC] py-20 sm:py-24">
      <div className="mx-auto max-w-7xl px-6 lg:px-8">

        {/* Heading */}
        <div className="text-center">
          <div className="mb-4 inline-flex items-center rounded-full border border-orange-200 bg-orange-50 px-4 py-1.5 text-sm font-semibold uppercase tracking-[0.2em] text-orange-500">
            Purpose
          </div>

          <h2 className="text-4xl font-black tracking-[-0.04em] text-slate-900 sm:text-5xl">
            Mission & Vision
          </h2>

          <p className="mx-auto mt-4 max-w-2xl text-base leading-7 text-slate-600">
            The principles that guide every line of code, every design
            decision, and every client conversation.
          </p>
        </div>

        {/* Cards */}
        <div className="mt-12 grid gap-6 md:grid-cols-2">

          {/* Mission */}
          <article className="rounded-[1.75rem] border border-slate-200 bg-white p-7 shadow-[0_20px_50px_rgba(15,23,42,0.05)] transition-transform duration-300 hover:-translate-y-1 hover:shadow-lg">

            <div className="flex h-14 w-14 items-center justify-center rounded-2xl bg-blue-50 text-blue-500">
              <svg
                width="24"
                height="24"
                fill="none"
                stroke="currentColor"
                strokeWidth="2"
                viewBox="0 0 24 24"
              >
                <circle cx="12" cy="12" r="8" />
                <circle cx="12" cy="12" r="4" />
                <circle cx="12" cy="12" r="1" />
              </svg>
            </div>

            <h3 className="mt-5 text-xl font-bold tracking-[-0.02em] text-slate-900">
              Our Mission
            </h3>

            <p className="mt-3 text-sm leading-7 text-slate-600">
             Deliver scalable, reliable & cost-effective digital solutions while building
             long-term client partnerships.
            </p>

          </article>

          {/* Vision */}
          <article className="rounded-[1.75rem] border border-slate-200 bg-white p-7 shadow-[0_20px_50px_rgba(15,23,42,0.05)] transition-transform duration-300 hover:-translate-y-1 hover:shadow-lg">

            <div className="flex h-14 w-14 items-center justify-center rounded-2xl bg-orange-50 text-orange-500">
              <svg
                width="24"
                height="24"
                fill="none"
                stroke="currentColor"
                strokeWidth="2"
                viewBox="0 0 24 24"
              >
                <path d="M1 12s4-7 11-7 11 7 11 7-4 7-11 7S1 12 1 12z" />
                <circle cx="12" cy="12" r="3" />
              </svg>
            </div>

            <h3 className="mt-5 text-xl font-bold tracking-[-0.02em] text-slate-900">
              Our Vision
            </h3>

            <p className="mt-3 text-sm leading-7 text-slate-600">
            To become a globally trusted technology and outsourcing partner.
            </p>

          </article>

        </div>

      </div>
    </section>
  );
}
