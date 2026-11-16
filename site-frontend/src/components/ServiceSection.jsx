import { useNavigate } from 'react-router-dom'

const services = [
  {
    id: 1,
    icon: (
      <svg width="28" height="28" fill="none" stroke="currentColor" strokeWidth="2" viewBox="0 0 24 24">
        <path d="M8 6L3 12L8 18" />
        <path d="M16 6L21 12L16 18" />
      </svg>
    ),
    title: "Website & Software Development",
    description:
      "Custom websites and software solutions designed to improve business operations, performance, and user experience.",
    color: "#3b82f6",
    bg: "rgba(59,130,246,0.08)",
  },

  {
    id: 2,
    icon: (
      <svg width="28" height="28" fill="none" stroke="currentColor" strokeWidth="2" viewBox="0 0 24 24">
        <rect x="7" y="2" width="10" height="20" rx="2" />
        <circle cx="12" cy="18" r="1" />
      </svg>
    ),
    title: "Mobile Application Development",
    description:
      "High-performance Android and iOS mobile applications built with intuitive design and seamless functionality.",
    color: "#7c3aed",
    bg: "rgba(124,58,237,0.08)",
  },

  {
    id: 3,
    icon: (
      <svg width="28" height="28" fill="none" stroke="currentColor" strokeWidth="2" viewBox="0 0 24 24">
        <circle cx="8" cy="8" r="2" />
        <circle cx="16" cy="8" r="2" />
        <circle cx="12" cy="16" r="2" />
        <line x1="8" y1="8" x2="12" y2="16" />
        <line x1="16" y1="8" x2="12" y2="16" />
      </svg>
    ),
    title: "CRM & ERP Solution",
    description:
      "Powerful CRM and ERP systems that automate workflows, improve productivity, and streamline business management.",
    color: "#0d9488",
    bg: "rgba(13,148,136,0.08)",
  },

  {
    id: 4,
    icon: (
      <svg width="28" height="28" fill="none" stroke="currentColor" strokeWidth="2" viewBox="0 0 24 24">
        <path d="M3 11L22 2L13 21L11 13L3 11Z" />
      </svg>
    ),
    title: "Digital Marketing Services",
    description:
      "Grow your online presence with SEO, social media marketing, paid advertising, branding, and content strategies.",
    color: "#a36f2a",
    bg: "rgba(163,111,42,0.08)",
  },

  {
    id: 5,
    icon: (
      <svg width="28" height="28" fill="none" stroke="currentColor" strokeWidth="2" viewBox="0 0 24 24">
        <path d="M4 12a8 8 0 0 1 16 0" />
        <rect x="2" y="11" width="4" height="7" rx="2" />
        <rect x="18" y="11" width="4" height="7" rx="2" />
        <path d="M12 20h2" />
      </svg>
    ),
    title: "BPO & Customer Support Solutions",
    description:
      "Professional customer support and business process outsourcing services that enhance customer satisfaction and efficiency.",
    color: "#9a236d",
    bg: "rgba(154,35,109,0.08)",
  },

  {
    id: 6,
    icon: (
      <svg width="28" height="28" fill="none" stroke="currentColor" strokeWidth="2" viewBox="0 0 24 24">
        <rect x="7" y="7" width="10" height="10" rx="2" />
        <path d="M12 2v3M12 19v3M2 12h3M19 12h3" />
        <path d="M5 5l2 2M17 17l2 2M19 5l-2 2M5 19l2-2" />
      </svg>
    ),
    title: "AI & Automation Services",
    description:
      "Intelligent AI-powered solutions and workflow automation to reduce manual effort, improve accuracy, and boost productivity.",
    color: "#52409b",
    bg: "rgba(82,64,155,0.08)",
  },
];
export default function ServicesSection() {
  const navigate = useNavigate()

  return (
    <section id="services" className="bg-white py-28">

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