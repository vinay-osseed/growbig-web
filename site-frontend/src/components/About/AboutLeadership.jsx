const leaders = [
  {
    name: "Sushant Paste",
    role: "Director",
    image: "",
  },
  {
    name: "Vinayak Jhadav",
    role: "Director | Operations Head",
    image: "",
  },
  {
    name: "Manish Jadhav",
    role: "HR Admin | HR Manager",
    image: "",
  },
  {
    name: "Heena Shaikh",
    role: "Manager",
    image: "",
  },
];

export default function AboutLeadershipSection() {
  return (
    <section className="bg-white py-20 sm:py-24">
      <div className="mx-auto max-w-7xl px-6 lg:px-8">
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

        {/* Cards */}
        <div className="mt-12 grid grid-cols-1 gap-8 sm:grid-cols-2 xl:grid-cols-4">
          {leaders.map((leader) => (
            <article
              key={leader.name}
              className="w-full max-w-[320px] overflow-hidden rounded-[28px] border border-slate-200 bg-white shadow-[0_15px_40px_rgba(15,23,42,0.06)] transition-all duration-300 hover:-translate-y-2 hover:shadow-xl">
              {/* Image */}
              <div className="h-[250px] overflow-hidden">
                <img
                  src={leader.image}
                  alt={leader.name}
                  className="h-full w-full object-cover transition duration-500 hover:scale-105"
                />
              </div>

              {/* Content */}
              <div className="p-5">
                <h3 className="text-xl font-bold tracking-[-0.02em] text-slate-900">
                  {leader.name}
                </h3>

                <p className="mt-1 text-sm font-semibold text-orange-500">
                  {leader.role}
                </p>

                <div className="mt-4 h-[3px] w-10 rounded-full bg-orange-500"></div>
              </div>
            </article>
          ))}
        </div>
      </div>
    </section>
  );
}