
import { useState } from "react";
import michealImg from "../../assets/micheal.png";
import VinayakImg from "../../assets/Vinayak.png";
import HeenaImg from "../../assets/Heena.png";
import sushantImg from "../../assets/sushant.png";

const leaders = [
  {
    id: 1,
    name: "Michael D'souza",
    role: "CEO",
    degree: "BSL,LL.B. , BBM",
    image: michealImg,
    description:
      "Michael D'Souza is the CEO of GrowBig Technologies, holding a B.S.L., LL.B. degree with expertise in law, governance, and business management. He is a successful entrepreneur in the agriculture sector and serves as an elected Member of the Zilla Parishad. Committed to innovation, ethical leadership, and social responsibility, he actively contributes to community welfare and sustainable development.",
  },
  {
    id: 2,
    name: "Sushant Paste",
    role: "Director",
    degree: "B.E. Computer Engineering",
    image: sushantImg,
    description:
      "Sushant Paste is the Director of GrowBig Technologies and holds a Bachelor of Engineering (B.E.) in Computer Science. With over 20 years of experience in the IT industry, he specializes in software solutions, project management, and technology-driven innovation. He is committed to driving operational excellence, digital transformation, and sustainable business growth through reliable and innovative technology solutions.",
  },
  {
    id: 3,
    name: "Vinayak Jadhav",
    role: "Director",
    degree: "M.Tech. Computer Engineering",
    image: VinayakImg,
    description:
      "Vinayak Jadhav is the Director of GrowBig Technologies, holding an M.Tech. and B.E. in Computer Science. With over 20 years of experience in the education and IT sectors, he has expertise in teaching, software development, and technical leadership. He is committed to driving innovation, technical excellence, and delivering high-quality technology solutions while fostering continuous learning and professional growth.",
  },
  {
    id: 5,
    name: "Heena Shaikh",
    role: "Recruitment officer",
    degree: "BA, MA-HISTORY",
    image: HeenaImg,
    description:
      "Heena Shaikh is the Recruitment Officer at GrowBig Technologies and holds a Bachelor of Arts (B.A.) and a Master of Arts (M.A.) in History. She is responsible for talent acquisition, candidate engagement, and supporting the company's hiring initiatives. With a strong focus on identifying the right talent and building a skilled workforce, she contributes to the organization's growth through effective recruitment and people-centric practices.",
  },
];

export default function AboutLeadershipSection() {
  const [openCard, setOpenCard] = useState(null);

  return (
    <section className="bg-white py-20 sm:py-24">
      <div className="mx-auto max-w-screen-2xl px-6 lg:px-8">
        {/* Heading */}
        <div className="text-center">
          <div className="mb-4 inline-flex items-center rounded-full border border-orange-200 bg-orange-50 px-4 py-1.5 text-sm font-semibold uppercase tracking-[0.2em] text-orange-500">
            THE TEAM
          </div>

          <h2 className="text-4xl font-black tracking-[-0.04em] text-slate-900 sm:text-5xl">
            Our Leadership
          </h2>

          <p className="mx-auto mt-4 max-w-2xl text-base leading-7 text-slate-600">
            The people steering strategy, technology, and culture at GrowBig.
          </p>
        </div>

        {/* Centered Leadership Cards */}
        <div className="mx-auto mt-12 grid w-full max-w-[1300px] grid-cols-1 items-start gap-6 sm:grid-cols-2 lg:grid-cols-4">
          {leaders.map((leader) => (
            <article
              key={leader.id}
              className="min-w-0 overflow-hidden rounded-[28px] border border-slate-200 bg-white shadow-[0_15px_40px_rgba(15,23,42,0.06)] transition-all duration-300 hover:-translate-y-2 hover:shadow-xl"
            >
              {/* Image */}
              <div className="h-[260px] overflow-hidden bg-slate-100 sm:h-[240px] lg:h-[220px]">
                <img
                  src={leader.image}
                  alt={leader.name}
                  className="h-full w-full object-cover object-top transition duration-500 hover:scale-105"
                />
              </div>

              {/* Content */}
              <div className="flex flex-col p-5">
                <h3 className="text-xl font-bold text-slate-900">
                  {leader.name}
                </h3>

                <p className="mt-1 text-sm font-semibold text-orange-500">
                  {leader.role}
                </p>

                <p className="mt-2 text-sm text-slate-500">
                  {leader.degree}
                </p>

                {/* Profile Toggle */}
                <button
                  type="button"
                  onClick={() =>
                    setOpenCard(
                      openCard === leader.id ? null : leader.id
                    )
                  }
                  aria-expanded={openCard === leader.id}
                  className="mt-5 ml-auto text-sm font-semibold text-orange-500 transition hover:text-orange-600"
                >
                  {openCard === leader.id
                    ? "Hide Profile ▲"
                    : "View Profile"}
                </button>

                {/* Profile Description */}
                <div
                  className={`overflow-hidden transition-all duration-500 ${
                    openCard === leader.id
                      ? "mt-4 max-h-96 opacity-100"
                      : "max-h-0 opacity-0"
                  }`}
                >
                  <div className="border-t border-slate-200 py-4">
                    <p className="mt-3 text-left text-sm leading-6 text-slate-600">
                      {leader.description}
                    </p>
                  </div>
                </div>
              </div>
            </article>
          ))}
        </div>
      </div>
    </section>
  );
}
