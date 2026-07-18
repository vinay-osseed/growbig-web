import { useNavigate } from 'react-router-dom'

const services = [
  {
    id: 1,
    icon: (
      <svg width="28" height="28" fill="none" stroke="currentColor" strokeWidth="2" viewBox="0 0 24 24">
        <rect x="3" y="3" width="18" height="18" rx="2" ry="2"/>
        <line x1="3" y1="9" x2="21" y2="9"/>
        <line x1="9" y1="21" x2="9" y2="9"/>
      </svg>
    ),
    title: 'Website & Software Development',
    description:
      'Pixel-perfect, high-performance websites built with modern frameworks — from landing pages to enterprise portals.',
    color: '#3b82f6',
    bg: 'rgba(59,130,246,0.08)',
  },
  {
    id: 2,
    icon: (
      <svg width="28" height="28" fill="none" stroke="currentColor" strokeWidth="2" viewBox="0 0 24 24">
        <rect x="5" y="2" width="14" height="20" rx="2" ry="2"/>
        <line x1="12" y1="18" x2="12.01" y2="18"/>
      </svg>
    ),
    title: 'Mobile Application Developmentt',
    description:
      'Native and cross-platform apps for iOS and Android, crafted with seamless UX and robust backend integration.',
    color: '#7c3aed',
    bg: 'rgba(124,58,237,0.08)',
  },
  {
    id: 3,
    icon: (
      <svg width="28" height="28" fill="none" stroke="currentColor" strokeWidth="2" viewBox="0 0 24 24">
        <polyline points="16 18 22 12 16 6"/>
        <polyline points="8 6 2 12 8 18"/>
      </svg>
    ),
    title: 'CRM & ERP Solution',
    description:
      'Tailored software solutions engineered around your business logic, workflows, and scaling requirements.',
    color: '#0d9488',
    bg: 'rgba(13,148,136,0.08)',
  },
   {
    id: 4,
    icon: (
      <svg width="28" height="28" fill="none" stroke="currentColor" strokeWidth="2" viewBox="0 0 24 24">
        <polyline points="16 18 22 12 16 6"/>
        <polyline points="8 6 2 12 8 18"/>
      </svg>
    ),
    title: 'Digital Marketing Services',
    description:
      'Tailored software solutions engineered around your business logic, workflows, and scaling requirements.',
    color: '#a36f2a',
    bg: 'rgba(13,148,136,0.08)',
  },
  {
    id: 5,
    icon: (
      <svg width="28" height="28" fill="none" stroke="currentColor" strokeWidth="2" viewBox="0 0 24 24">
        <polyline points="16 18 22 12 16 6"/>
        <polyline points="8 6 2 12 8 18"/>
      </svg>
    ),
    title: 'BPO & Customer Support Solutions',
    description:
      'Tailored software solutions engineered around your business logic, workflows, and scaling requirements.',
    color: '#9a236d',
    bg: 'rgba(13,148,136,0.08)',
  },
   {
    id: 6,
    icon: (
      <svg width="28" height="28" fill="none" stroke="currentColor" strokeWidth="2" viewBox="0 0 24 24">
        <polyline points="16 18 22 12 16 6"/>
        <polyline points="8 6 2 12 8 18"/>
      </svg>
    ),
    title: 'AI & Automation Services',
    description:
      'Tailored software solutions engineered around your business logic, workflows, and scaling requirements.',
    color: '#9a236d',
    bg: 'rgba(13,148,136,0.08)',
  },
]

export default function ServicesSection() {
  const navigate = useNavigate()

  return (
    <section className="bg-white py-28">

      <div className="max-w-[1440px] mx-auto px-8 lg:px-12">

        {/* Badge */}

        <div className="flex justify-center">
          <div className="px-7 py-2 border border-orange-200 rounded-full bg-orange-50">
            <p className="text-[16px] font-medium tracking-wide text-orange-500 uppercase">
              WHAT WE DO
            </p>
          </div>
        </div>

        {/* Heading */}

        <h2 className="mt-8 text-center text-[35px] font-black text-[#0F172A] leading-none">
          Our Services
        </h2>

        {/* Description */}

        <p className="mt-8 max-w-[760px] mx-auto text-center text-[19px] leading-10 text-[#64748B]">
          We build scalable digital products that transform how businesses
          connect with their customers.
        </p>

        <div className="mt-12 grid gap-6 md:grid-cols-2 xl:grid-cols-3">
          {services.map((s) => (
            <div key={s.id} className="group rounded-[1.75rem] border border-slate-200 bg-white p-7 shadow-sm transition-all duration-300 hover:-translate-y-1 hover:border-slate-300 hover:shadow-xl">
              <div className="flex h-16 w-16 items-center justify-center rounded-2xl" style={{ background: s.bg, color: s.color }}>
                {s.icon}
              </div>
              <h3 className="mt-5 text-2xl font-bold tracking-tight text-slate-900">{s.title}</h3>
              <p className="mt-3 text-base leading-7 text-slate-600">{s.description}</p>
              {/*<button
                className="mt-6 inline-flex items-center gap-1.5 text-sm font-semibold transition-all duration-200 hover:gap-3"
                style={{ color: s.color }}
                onClick={() => navigate('/#services')}
              >
                Learn more
                <svg width="16" height="16" fill="none" stroke="currentColor" strokeWidth="2.5" viewBox="0 0 24 24">
                  <line x1="5" y1="12" x2="19" y2="12"/>
                  <polyline points="12 5 19 12 12 19"/>
                </svg>
              </button>*/}
            </div>
          ))}
        </div>
      </div>
    </section>
  )
}