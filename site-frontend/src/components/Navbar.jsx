import { useState, useEffect } from 'react'
import { Link, NavLink, useNavigate } from 'react-router-dom'
import logo from "../assets/logo.png";

const navLinks = [
  { label: 'Home', to: '/' },
  { label: 'About', to: '/about' },
  { label: 'Services', to: '/services' },
  { label: 'Careers', to: '/careers' },
  { label: 'Our Leadership', to: '/leadership' },
  { label: 'Partners', to: '/partners' },
  { label: 'Contact', to: '/contact' },
]

export default function Navbar() {
  const [menuOpen, setMenuOpen] = useState(false)
  const [scrolled, setScrolled] = useState(false)
  const navigate = useNavigate()

  useEffect(() => {
    const handleScroll = () => setScrolled(window.scrollY > 20)
    window.addEventListener('scroll', handleScroll)
    return () => window.removeEventListener('scroll', handleScroll)
  }, [])

  useEffect(() => {
    if (menuOpen) {
      document.body.style.overflow = 'hidden'
    } else {
      document.body.style.overflow = ''
    }
    return () => { document.body.style.overflow = '' }
  }, [menuOpen])

  const handleNavClick = () => setMenuOpen(false)

  return (
    <header
      className={`sticky top-0 z-50 border-b transition-all duration-300 ${
        scrolled
          ? 'border-gray-200 bg-white shadow-md'
          : 'border-gray-200 bg-gray-100'
      }`}
    >
      <div className="mx-auto flex max-w-7xl items-center justify-between px-6 py-4 lg:px-8">
        
          <Link
  to="/"
  onClick={handleNavClick}
  className="flex items-center"
>
  <img
    src={logo}
    alt="GrowBig Technologies"
    className="h-14 w-auto object-contain"
  />
</Link>

        <nav className="hidden items-center gap-7 lg:flex">
          {navLinks.map((link) => (
            <NavLink
              key={link.to}
              to={link.to}
              className={({ isActive }) =>
                `relative text-sm font-medium transition-colors duration-200 hover:text-blue-700 ${
                  isActive
                    ? 'text-blue-700 font-semibold pb-0.5 border-b-2 border-orange-500'
                    : 'text-slate-600'
                }`
              }
            >
              {link.label}
            </NavLink>
          ))}
        </nav>

        <div className="hidden lg:block">
          <button
            className="inline-flex items-center gap-2 rounded-full bg-blue-600 px-5 py-2.5 text-sm font-semibold text-white shadow-lg shadow-blue-900/20 transition hover:-translate-y-0.5 hover:bg-blue-700"
            onClick={() => navigate('/contact')}
          >
            Get Started
            <svg width="16" height="16" fill="none" stroke="currentColor" strokeWidth="2.5" viewBox="0 0 24 24">
              <line x1="5" y1="12" x2="19" y2="12"/>
              <polyline points="12 5 19 12 12 19"/>
            </svg>
          </button>
        </div>

        <button
          className="inline-flex h-11 w-11 items-center justify-center rounded-xl border border-gray-300 bg-gray-200 text-slate-700 transition hover:bg-gray-300 lg:hidden"
          onClick={() => setMenuOpen(!menuOpen)}
          aria-label="Toggle menu"
        >
          <span className="flex flex-col gap-1.5">
            <span className={`h-0.5 w-5 rounded-full bg-current transition ${menuOpen ? 'translate-y-2 rotate-45' : ''}`} />
            <span className={`h-0.5 w-5 rounded-full bg-current transition ${menuOpen ? 'opacity-0' : ''}`} />
            <span className={`h-0.5 w-5 rounded-full bg-current transition ${menuOpen ? '-translate-y-2 -rotate-45' : ''}`} />
          </span>
        </button>
      </div>

      <div
        className={`overflow-hidden border-t border-gray-200 bg-white transition-all duration-300 lg:hidden ${
          menuOpen ? 'max-h-[520px] opacity-100' : 'max-h-0 opacity-0'
        }`}
      >
        <nav className="mx-auto flex max-w-7xl flex-col gap-2 px-6 py-4">
          {navLinks.map((link) => (
            <NavLink
              key={link.to}
              to={link.to}
              className={({ isActive }) =>
                `rounded-2xl px-4 py-3 text-sm font-medium transition ${
                  isActive ? 'bg-blue-50 text-blue-700 font-semibold' : 'text-slate-700 hover:bg-gray-100 hover:text-blue-700'
                }`
              }
              onClick={handleNavClick}
            >
              {link.label}
            </NavLink>
          ))}
          <button
            className="mt-2 inline-flex items-center justify-center gap-2 rounded-2xl bg-blue-600 px-4 py-3 text-sm font-semibold text-white transition hover:bg-blue-700"
            onClick={() => { navigate('/contact'); handleNavClick() }}
          >
            Get Started
            <svg width="16" height="16" fill="none" stroke="currentColor" strokeWidth="2.5" viewBox="0 0 24 24">
              <line x1="5" y1="12" x2="19" y2="12"/>
              <polyline points="12 5 19 12 12 19"/>
            </svg>
          </button>
        </nav>
      </div>
    </header>
  )
}
