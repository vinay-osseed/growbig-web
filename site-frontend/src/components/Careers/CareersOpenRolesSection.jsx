// import { useState } from 'react'
// import CareersRoleFilters from './CareersRoleFilters'
// import CareersRoleCard from './CareersRoleCard'

// const categories = ['All', 'Engineering', 'Design', 'Mobile', 'Marketing']

// const positions = [
//   {
//     title: 'Senior React Developer',
//     department: 'Engineering',
//     type: 'Full-Time',
//     location: 'Sawantwadi / Remote',
//     experience: '2-5 Years',
//     posted: 'Posted 2 days ago',
//     badge: 'Hybrid',
//   },
//   {
//     title: 'Node.js Backend Engineer',
//     department: 'Engineering',
//     type: 'Full-Time',
//     location: 'Anywhere in India',
//     experience: '3-6 Years',
//     posted: 'Posted 3 weeks ago',
//     badge: 'Remote',
//   },
//   {
//     title: 'Product Designer',
//     department: 'Design',
//     type: 'Full-Time',
//     location: 'Sawantwadi',
//     experience: '2-4 Years',
//     posted: 'Posted 1 week ago',
//     badge: 'On-site',
//   },
//   {
//     title: 'React Native Developer',
//     department: 'Mobile',
//     type: 'Full-Time',
//     location: 'Sawantwadi / Remote',
//     experience: '1-3 Years',
//     posted: 'Posted 5 days ago',
//     badge: 'Hybrid',
//   },
//   {
//     title: 'Digital Marketing Executive',
//     department: 'Marketing',
//     type: 'Full-Time',
//     location: 'Sawantwadi',
//     experience: '1-2 Years',
//     posted: 'Posted 1 week ago',
//     badge: 'On-site',
//   },
//   {
//     title: 'DevOps Engineer',
//     department: 'Engineering',
//     type: 'Full-Time',
//     location: 'Remote',
//     experience: '3-5 Years',
//     posted: 'Posted 4 days ago',
//     badge: 'Remote',
//   },
// ]

// export default function CareersOpenRolesSection() {
//   const [activeCategory, setActiveCategory] = useState('All')

//   const visiblePositions =
//     activeCategory === 'All'
//       ? positions
//       : positions.filter((position) => position.department === activeCategory)

//   return (
//     <section className="bg-white px-6 py-20 sm:py-24 lg:px-8">
//       <div className="mx-auto max-w-7xl text-center">
//         <div className="inline-flex rounded-full border border-amber-200 bg-amber-50 px-5 py-2 text-sm font-semibold uppercase tracking-[0.18em] text-orange-600">
//           Open Roles
//         </div>
//         <h2 className="mt-6 text-3xl font-black tracking-[-0.05em] text-slate-950 sm:text-5xl lg:text-5xl">
//           Current Openings
//         </h2>
//         <p className="mx-auto mt-4 max-w-2xl text-lg leading-8 text-slate-600">
//           Find a role that matches your skill, and grow alongside a team that has your back.
//         </p>

//         <CareersRoleFilters categories={categories} activeCategory={activeCategory} onChange={setActiveCategory} />

//         <div className="mt-14 space-y-6 text-left">
//           {visiblePositions.map((position) => (
//             <CareersRoleCard
//               key={position.title}
//               position={position}
//             />
//           ))}
//         </div>
//       </div>
//     </section>
//   )
// }

import { FiMapPin, FiBriefcase, FiClock } from "react-icons/fi";

export default function CareersOpenRolesSection() {
  return (
    <section className="bg-white py-28">
      <div className="mx-auto max-w-7xl px-6">
        {/* Badge */}
        <div className="flex justify-center">
          <span className="rounded-full border border-[#FDBA74] bg-[#FFF7ED] px-6 py-2 text-[15px] font-semibold uppercase tracking-[2px] text-[#F97316]">
            Open Roles
          </span>
        </div>

        {/* Heading */}
        <h2 className="mt-6 text-center text-[48px] font-extrabold text-[#0B1F4D]">
          Current Openings
        </h2>

        <p className="mx-auto mt-6 max-w-3xl text-center text-[22px] leading-10 text-[#64748B]">
          Join our growing team and build your career with GrowBig
          Technologies.
        </p>

        {/* Job Card */}
        <div className="mx-auto mt-20 max-w-4xl rounded-[32px] border border-[#E5E7EB] bg-white p-10 shadow-[0_15px_50px_rgba(15,23,42,0.08)] transition hover:-translate-y-1 hover:shadow-[0_20px_60px_rgba(15,23,42,0.12)]">
          <div className="flex flex-col gap-8 md:flex-row md:items-center md:justify-between">
            <div>
              <h3 className="text-[34px] font-bold text-[#0B1F4D]">
                Customer Support Executive
              </h3>

              <div className="mt-5 flex flex-wrap gap-5 text-[16px] text-[#64748B]">
                <span className="flex items-center gap-2">
                  <FiMapPin />
                  Sawantwadi, Maharashtra
                </span>

                <span className="flex items-center gap-2">
                  <FiBriefcase />
                  Rotational Shifts
                </span>

                <span className="flex items-center gap-2">
                  <FiClock />
                  0–2 Years Experience
                </span>
              </div>

              <p className="mt-8 max-w-2xl text-[18px] leading-8 text-[#475569]">
                We are looking for a Customer Support Executive with excellent
                communication skills to assist customers, resolve queries, and
                deliver outstanding service while working closely with our
                internal teams.
              </p>
            </div>

            <div className="flex justify-center">
              <button className="rounded-full bg-[#2563EB] px-8 py-4 text-[17px] font-semibold text-white transition hover:bg-[#1D4ED8]">
                Apply Now
              </button>
            </div>
          </div>
        </div>

        {/* Bottom Note */}
        <div className="mt-12 text-center">
          <p className="text-[18px] text-[#64748B]">
            🚀 More exciting career opportunities will be announced soon. Stay
            connected with GrowBig Technologies.
          </p>
        </div>
      </div>
    </section>
  );
}