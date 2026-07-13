import { useEffect } from 'react'
import { useLocation } from 'react-router-dom'

export default function ScrollToTop() {
  const { pathname, hash } = useLocation()

  useEffect(() => {
    const timer = window.setTimeout(() => {
      if (hash) {
        const target = document.getElementById(hash.slice(1))
        if (target) {
          target.scrollIntoView({ block: 'start', behavior: 'auto' })
          return
        }
      }

      window.scrollTo(0, 0)
    }, 0)

    return () => window.clearTimeout(timer)
  }, [pathname, hash])

  return null
}