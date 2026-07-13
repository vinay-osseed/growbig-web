import { useState, useEffect } from 'react'
import { useNavigate } from 'react-router-dom'

const slides = [
  {
    label: 'Website Development',
    badge: 'Web Experts',
    img: '',
  },
  {
    label: 'Expert Team',
    badge: 'Expert Team',
    img: '',
  },
  {
    label: 'Custom Software',
    badge: 'Custom Dev',
    img: '',
  },
  {
    label: 'Cloud Solutions',
    badge: 'Cloud First',
    img: '',
  },
  {
    label: 'AI-Powered',
    badge: 'AI Driven',
    img: '',
  },
]

export default function HeroSection() {
  const [activeSlide, setActiveSlide] = useState(1)
  const navigate = useNavigate()

  useEffect(() => {
    const timer = setInterval(() => {
      setActiveSlide((prev) => (prev + 1) % slides.length)
    }, 3500)
    return () => clearInterval(timer)
  }, [])

  return (
    <section className="relative overflow-hidden bg-[#071a3e] pb-20 pt-20 text-white md:pb-28 md:pt-24">
      {/* Grid pattern */}
      <div className="absolute inset-0 bg-[linear-gradient(rgba(255,255,255,0.04)_1px,transparent_1px),linear-gradient(90deg,rgba(255,255,255,0.04)_1px,transparent_1px)] bg-[size:56px_56px] opacity-60" />
      {/* Ambient color glows */}
      <div className="absolute inset-0 bg-[radial-gradient(ellipse_at_70%_20%,rgba(59,130,246,0.22),transparent_50%),radial-gradient(ellipse_at_15%_80%,rgba(249,115,22,0.14),transparent_45%)]" />

      <div className="relative mx-auto grid max-w-7xl items-center gap-12 px-6 lg:grid-cols-[1.05fr_0.95fr] lg:gap-16 lg:px-8">

        {/* ── Left content ── */}
        <div>
          {/* Badge */}
          <div className="inline-flex items-center gap-3 rounded-full border border-white/10 bg-white/5 px-4 py-2 text-sm font-medium text-slate-200 backdrop-blur-sm">
            <span className="h-2.5 w-2.5 rounded-full bg-orange-400" />
            Premium IT Solutions · Est. 2020
          </div>

          {/* Heading */}
          <h1 className="mt-4 text-xl font-black leading-[1.08] tracking-[-0.04em] text-white sm:text-2xl lg:text-6xl">
            Building Digital{' '}
            <span className="text-sky-400">Solutions</span>{' '}
            That{' '}
            <span className="text-orange-400">Drive</span>{' '}
            Business Growth
          </h1>

          {/* Subtitle */}
          <p className="mt-6 max-w-xl text-base leading-8 text-slate-300 lg:text-lg">
            GrowBig Technologies LLP delivers modern websites, mobile applications, cloud
            solutions, digital transformation, AI-powered software, branding, and technology
            consulting for startups and enterprises.
          </p>

          {/* Buttons */}
          <div className="mt-8 flex flex-wrap gap-4">
            <button
              className="inline-flex items-center gap-2 rounded-full bg-blue-600 px-7 py-3.5 text-sm font-semibold text-white shadow-lg shadow-blue-900/30 transition hover:-translate-y-0.5 hover:bg-blue-700"
              onClick={() => navigate('/contact')}
            >
              Start Your Project
              <svg width="17" height="17" fill="none" stroke="currentColor" strokeWidth="2.5" viewBox="0 0 24 24">
                <line x1="5" y1="12" x2="19" y2="12" />
                <polyline points="12 5 19 12 12 19" />
              </svg>
            </button>
            <button
              className="inline-flex items-center gap-2 rounded-full border border-slate-600 bg-slate-800/70 px-7 py-3.5 text-sm font-semibold text-orange-400 transition hover:-translate-y-0.5 hover:bg-slate-700"
              onClick={() => navigate('/services')}
            >
              View Services
              <svg width="17" height="17" fill="none" stroke="currentColor" strokeWidth="2.5" viewBox="0 0 24 24">
                <line x1="5" y1="12" x2="19" y2="12" />
                <polyline points="12 5 19 12 12 19" />
              </svg>
            </button>
          </div>

          {/* Stats — plain, no container */}
          <div className="mt-10 flex flex-wrap items-center gap-8">
            <div>
              <span className="block text-3xl font-extrabold tracking-tight text-white">150+</span>
              <span className="mt-1 block text-sm text-slate-400">Projects Delivered</span>
            </div>
            <div className="hidden h-10 w-px bg-white/15 sm:block" />
            <div>
              <span className="block text-3xl font-extrabold tracking-tight text-white">50+</span>
              <span className="mt-1 block text-sm text-slate-400">Happy Clients</span>
            </div>
            <div className="hidden h-10 w-px bg-white/15 sm:block" />
            <div>
              <span className="block text-3xl font-extrabold tracking-tight text-white">6+</span>
              <span className="mt-1 block text-sm text-slate-400">Years Experience</span>
            </div>
          </div>
        </div>

        {/* ── Right: Photo slideshow ── */}
        <div className="mx-auto w-full max-w-xl lg:max-w-none">
          {/* Photo frame */}
          <div className="relative overflow-hidden rounded-[1.75rem] shadow-2xl shadow-black/50">
            <img
              key={activeSlide}
              src={slides[activeSlide].img}
              alt={slides[activeSlide].label}
              className="h-[360px] w-full object-cover sm:h-[440px] lg:h-[500px]"
              style={{ animation: 'slideImgFade 0.55s ease forwards' }}
            />
            {/* Bottom-to-top dark gradient for badge legibility */}
            <div className="absolute inset-0 bg-gradient-to-t from-black/65 via-black/10 to-transparent" />

            {/* Counter — top right */}
            <div className="absolute right-4 top-4 rounded-full bg-black/35 px-3 py-1 text-xs font-semibold text-white backdrop-blur-sm">
              {activeSlide + 1} / {slides.length}
            </div>

            {/* Service badge — bottom left */}
            <div className="absolute bottom-4 left-4 rounded-full bg-orange-500 px-4 py-1.5 text-sm font-bold text-white shadow-lg">
              {slides[activeSlide].badge}
            </div>
          </div>

          {/* Pill-style slide dots */}
          <div className="mt-5 flex items-center justify-center gap-2">
            {slides.map((_, i) => (
              <button
                key={i}
                onClick={() => setActiveSlide(i)}
                aria-label={`Go to slide ${i + 1}`}
                className={`h-2 rounded-full transition-all duration-300 focus:outline-none ${
                  i === activeSlide ? 'w-6 bg-orange-500' : 'w-2 bg-white/30 hover:bg-white/55'
                }`}
              />
            ))}
          </div>
        </div>
      </div>

    </section>
  )
}
