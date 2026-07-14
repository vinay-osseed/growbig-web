import { useState } from 'react'
import CareersRoleFilters from './CareersRoleFilters'
import CareersRoleCard from './CareersRoleCard'

const categories = ['All', 'Engineering', 'Design', 'Mobile', 'Marketing']

const positions = [
  {
    title: 'Senior React Developer',
    department: 'Engineering',
    type: 'Full-Time',
    location: 'Sawantwadi / Remote',
    experience: '2-5 Years',
    posted: 'Posted 2 days ago',
    badge: 'Hybrid',
  },
  {
    title: 'Node.js Backend Engineer',
    department: 'Engineering',
    type: 'Full-Time',
    location: 'Anywhere in India',
    experience: '3-6 Years',
    posted: 'Posted 3 weeks ago',
    badge: 'Remote',
  },
  {
    title: 'Product Designer',
    department: 'Design',
    type: 'Full-Time',
    location: 'Sawantwadi',
    experience: '2-4 Years',
    posted: 'Posted 1 week ago',
    badge: 'On-site',
  },
  {
    title: 'React Native Developer',
    department: 'Mobile',
    type: 'Full-Time',
    location: 'Sawantwadi / Remote',
    experience: '1-3 Years',
    posted: 'Posted 5 days ago',
    badge: 'Hybrid',
  },
  {
    title: 'Digital Marketing Executive',
    department: 'Marketing',
    type: 'Full-Time',
    location: 'Sawantwadi',
    experience: '1-2 Years',
    posted: 'Posted 1 week ago',
    badge: 'On-site',
  },
  {
    title: 'DevOps Engineer',
    department: 'Engineering',
    type: 'Full-Time',
    location: 'Remote',
    experience: '3-5 Years',
    posted: 'Posted 4 days ago',
    badge: 'Remote',
  },
]

export default function CareersOpenRolesSection() {
  const [activeCategory, setActiveCategory] = useState('All')

  const visiblePositions =
    activeCategory === 'All'
      ? positions
      : positions.filter((position) => position.department === activeCategory)

  return (
    <section className="bg-white px-6 py-20 sm:py-24 lg:px-8">
      <div className="mx-auto max-w-7xl text-center">
        <div className="inline-flex rounded-full border border-amber-200 bg-amber-50 px-5 py-2 text-sm font-semibold uppercase tracking-[0.18em] text-orange-600">
          Open Roles
        </div>
        <h2 className="mt-6 text-3xl font-black tracking-[-0.05em] text-slate-950 sm:text-5xl lg:text-5xl">
          Current Openings
        </h2>
        <p className="mx-auto mt-4 max-w-2xl text-lg leading-8 text-slate-600">
          Find a role that matches your skill, and grow alongside a team that has your back.
        </p>

        <CareersRoleFilters categories={categories} activeCategory={activeCategory} onChange={setActiveCategory} />

        <div className="mt-14 space-y-6 text-left">
          {visiblePositions.map((position) => (
            <CareersRoleCard
              key={position.title}
              position={position}
            />
          ))}
        </div>
      </div>
    </section>
  )
}