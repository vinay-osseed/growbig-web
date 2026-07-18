export default function CareersHeroSection() {
  return (
    <section className="relative overflow-hidden bg-[#0f172a] text-white">
      <div className="absolute inset-0 bg-[linear-gradient(rgba(59,130,246,0.08)_1px,transparent_1px),linear-gradient(90deg,rgba(59,130,246,0.08)_1px,transparent_1px)] bg-[size:64px_64px] opacity-35" />
      <div className="absolute inset-0 bg-[radial-gradient(circle_at_15%_18%,rgba(37,99,235,0.32),transparent_32%),radial-gradient(circle_at_82%_20%,rgba(249,115,22,0.2),transparent_20%),linear-gradient(180deg,rgba(15,23,42,0.25),rgba(15,23,42,0.9))]" />

      <div className="relative mx-auto grid max-w-7xl gap-12 px-6 py-16 lg:grid-cols-[1.08fr_0.92fr] lg:items-center lg:px-8 lg:py-24">
        <div className="max-w-2xl">
          <div className="inline-flex items-center gap-2 rounded-full border border-amber-500/35 bg-amber-500/10 px-4 py-2 text-sm font-semibold text-amber-300 shadow-[0_0_0_1px_rgba(245,158,11,0.08)]">
            <span className="text-base leading-none">◼</span>
            We’re Hiring · 6 Open Roles
          </div>

          <h1 className="mt-8 text-5xl font-black tracking-[-0.05em] text-white sm:text-6xl lg:text-7xl">
            Build Your Career at
            <span className="block bg-[linear-gradient(90deg,#2563eb_0%,#3b82f6_38%,#f59e0b_100%)] bg-clip-text text-transparent">
              GrowBig
            </span>
          </h1>

          <p className="mt-6 max-w-xl text-lg leading-8 text-slate-300 sm:text-xl">
            Join a team that builds products used by real businesses, ships fast, and takes craftsmanship seriously. We hire for attitude and grow the skill.
          </p>

          {/*<div className="mt-10 grid max-w-xl grid-cols-3 gap-6">
            <div>
              <div className="text-3xl font-black tracking-[-0.04em] text-white">30+</div>
              <div className="mt-1 text-sm text-slate-400">Team Members</div>
            </div>
            <div>
              <div className="text-3xl font-black tracking-[-0.04em] text-white">5★</div>
              <div className="mt-1 text-sm text-slate-400">Glassdoor Rating</div>
            </div>
            <div>
              <div className="text-3xl font-black tracking-[-0.04em] text-white">92%</div>
              <div className="mt-1 text-sm text-slate-400">Retention Rate</div>
            </div>
          </div>*/}
        </div>

        <div className="relative lg:justify-self-end">
          <div className="absolute -left-4 top-10 hidden h-28 w-28 rounded-full bg-blue-500/15 blur-3xl lg:block" />
          <div className="absolute -right-10 bottom-6 hidden h-36 w-36 rounded-full bg-amber-500/15 blur-3xl lg:block" />

          <div className="relative overflow-hidden rounded-[2.15rem] border border-white/10 bg-[#d8dbe1] shadow-[0_30px_80px_rgba(0,0,0,0.35)]">
            <div className="absolute inset-0 bg-[linear-gradient(180deg,rgba(255,255,255,0.22),rgba(255,255,255,0)_20%)]" />

            <div className="relative aspect-[1.03/1] min-h-[430px] bg-[linear-gradient(180deg,#f3f4f6_0%,#edf0f4_52%,#d6dbe3_100%)]">
              <div className="absolute left-0 top-0 h-full w-full bg-[radial-gradient(circle_at_30%_22%,rgba(255,255,255,0.65),transparent_22%),radial-gradient(circle_at_70%_18%,rgba(255,255,255,0.75),transparent_18%)]" />
              <div className="absolute inset-0 bg-[linear-gradient(90deg,transparent_0%,transparent_19%,rgba(56,65,79,0.12)_19%,rgba(56,65,79,0.12)_22%,transparent_22%,transparent_49%,rgba(56,65,79,0.12)_49%,rgba(56,65,79,0.12)_53%,transparent_53%,transparent_100%),linear-gradient(180deg,transparent_0%,transparent_16%,rgba(56,65,79,0.12)_16%,rgba(56,65,79,0.12)_20%,transparent_20%,transparent_100%)]" />

              <div className="absolute left-0 top-[22%] h-px w-full bg-slate-500/25" />
              <div className="absolute left-[25%] top-0 h-full w-px bg-slate-500/25" />
              <div className="absolute left-[52%] top-0 h-full w-px bg-slate-500/25" />
              <div className="absolute left-[78%] top-0 h-full w-px bg-slate-500/25" />

              <div className="absolute inset-x-0 bottom-0 h-2/5 bg-[linear-gradient(180deg,rgba(6,11,22,0),rgba(6,11,22,0.45)_24%,rgba(6,11,22,0.92)_100%)]" />

              <div className="absolute left-5 top-5 rounded-full border border-amber-400/40 bg-[#f59e0b]/90 px-4 py-2 text-sm font-semibold text-white shadow-lg shadow-amber-900/35">
                ☀ Great Place to Work
              </div>

              <div className="absolute right-5 top-5 rounded-full border border-white/20 bg-slate-700/65 px-4 py-2 text-sm font-semibold text-amber-300 shadow-lg shadow-slate-900/20 backdrop-blur-sm">
                Great Place to Work
              </div>

              <div className="absolute left-[14%] top-[35%] h-[34%] w-[18%] rounded-[1.1rem] bg-slate-800 shadow-[0_18px_40px_rgba(15,23,42,0.32)]">
                <div className="mx-auto mt-2 h-[88%] w-[84%] rounded-lg bg-slate-950/90 p-3">
                  <div className="h-2 w-16 rounded-full bg-slate-700" />
                  <div className="mt-4 space-y-2">
                    <div className="h-2 rounded-full bg-slate-700/70" />
                    <div className="h-2 rounded-full bg-slate-700/60" />
                    <div className="h-2 w-3/4 rounded-full bg-slate-700/50" />
                  </div>
                </div>
              </div>

              <div className="absolute left-[44%] top-[38%] h-[27%] w-[20%] rounded-[1.1rem] bg-slate-800 shadow-[0_18px_40px_rgba(15,23,42,0.28)]">
                <div className="mx-auto mt-2 h-[87%] w-[84%] rounded-lg bg-[#0f172a] p-3">
                  <div className="flex gap-2">
                    <div className="h-2 w-10 rounded-full bg-slate-700" />
                    <div className="h-2 w-6 rounded-full bg-slate-700/70" />
                  </div>
                  <div className="mt-4 h-32 rounded-lg bg-[linear-gradient(180deg,rgba(37,99,235,0.35),rgba(15,23,42,0.15))]" />
                </div>
              </div>

              <div className="absolute right-[11%] top-[34%] h-[30%] w-[18%] rounded-[1rem] bg-slate-900 shadow-[0_18px_40px_rgba(15,23,42,0.3)]">
                <div className="mx-auto mt-2 h-[88%] w-[86%] rounded-lg bg-slate-100 p-2">
                  <div className="h-2 w-full rounded-full bg-slate-300" />
                  <div className="mt-3 space-y-2">
                    <div className="h-2 rounded-full bg-slate-300" />
                    <div className="h-2 rounded-full bg-slate-300/80" />
                    <div className="h-2 w-4/5 rounded-full bg-slate-300/70" />
                  </div>
                </div>
              </div>

              <div className="absolute bottom-[7%] left-[18%] h-[19%] w-[60%] rounded-[1.6rem] bg-[radial-gradient(circle_at_45%_25%,rgba(15,23,42,0.9),rgba(15,23,42,0.98)_60%)] shadow-[0_-18px_40px_rgba(15,23,42,0.28)]">
                <div className="absolute left-[24%] top-[10%] h-[76%] w-[26%] rounded-[1rem] bg-slate-950/90" />
                <div className="absolute left-[50%] top-[8%] h-[84%] w-[18%] rounded-[1rem] bg-slate-950/90" />
                <div className="absolute right-[14%] top-[18%] h-[62%] w-[16%] rounded-[1rem] bg-slate-800/95" />
                <div className="absolute left-[40%] top-[26%] h-[54%] w-[10%] rounded-full bg-slate-900/95" />
                <div className="absolute left-[46%] top-[30%] h-[46%] w-[2.5%] rounded-full bg-slate-700/90" />
                <div className="absolute left-[49%] top-[29%] h-[48%] w-[4.5%] rounded-[1rem] bg-slate-900/95" />
                <div className="absolute left-[12%] top-[22%] h-[28%] w-[18%] rounded-[999px] bg-[radial-gradient(circle_at_40%_40%,rgba(255,255,255,0.12),rgba(255,255,255,0)_70%)] blur-[1px]" />
              </div>

              <div className="absolute bottom-[14%] left-[8%] h-[12%] w-[18%] rounded-[1rem] bg-[linear-gradient(180deg,rgba(15,23,42,0.95),rgba(15,23,42,0.55))] shadow-lg" />
              <div className="absolute bottom-[12%] right-[10%] h-[11%] w-[15%] rounded-[1rem] bg-[linear-gradient(180deg,rgba(15,23,42,0.95),rgba(15,23,42,0.55))] shadow-lg" />
            </div>
          </div>
        </div>
      </div>
    </section>
  )
}
