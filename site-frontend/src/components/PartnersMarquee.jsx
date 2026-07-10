const partners = [
  {
    name: "AWS",
    bg: "bg-[#F9A826]",
    text: "AWS",
  },
  {
    name: "Meta",
    bg: "bg-[#1877F2]",
    text: "Me",
  },
  {
    name: "Adobe",
    bg: "bg-[#FF0000]",
    text: "Ad",
  },
  {
    name: "Cisco",
    bg: "bg-[#28A8E0]",
    text: "Cs",
  },
    {
    name: "Microsoft",
    bg: "bg-[#1DA1F2]",
    text: "MS",
  },

  {
    name: "Google",
    bg: "bg-[#4A7BEF]",
    text: "G",
  },
];

export default function PartnersMarquee() {
  return (
    <section className="bg-[#FAFAFA] py-28 overflow-hidden">
      <div className="max-w-7xl mx-auto px-6">

        {/* Badge */}
        <div className="flex justify-center">
          <span className="rounded-full border border-[#FDBA74] bg-[#FFF7ED] px-6 py-2 text-[15px] font-semibold tracking-[2px] uppercase text-[#F97316]">
            Ecosystem
          </span>
        </div>

        {/* Heading */}
        <h2 className="mt-6 text-center text-[45px] font-extrabold leading-none tracking-[-2px] text-[#0B1F4D]">
          Our Trusted Partners
        </h2>

        {/* Description */}
        <p className="mx-auto mt-7 max-w-[760px] text-center text-[24px] leading-[42px] text-[#64748B]">
          We collaborate with industry-leading technology partners to
          deliver best-in-class solutions.
        </p>
      </div>

      {/* Partners */}
      <div className="relative mt-24 overflow-hidden">

        <div className="absolute left-0 top-0 z-20 h-full w-40 bg-gradient-to-r from-[#FAFAFA] to-transparent" />

        <div className="absolute right-0 top-0 z-20 h-full w-40 bg-gradient-to-l from-[#FAFAFA] to-transparent" />

        <div className="flex w-max animate-marquee gap-8">
          {[...partners, ...partners].map((partner, index) => (
            <div
             key={index}
             className="flex h-[68px] w-[200px] shrink-0 items-center rounded-2xl border border-[#E5E7EB] bg-white px-4 shadow-[0_8px_24px_rgba(15,23,42,0.05)]"
            >
            <div
             className={`flex h-10 w-10 shrink-0 items-center justify-center rounded-full ${partner.bg}`}
            >
           <span className="text-[13px] font-bold text-white">
          {partner.text}
           </span>
           </div>

          <span className="ml-3 whitespace-nowrap text-[18px] font-semibold text-[#1E293B]">
          {partner.name}
          </span>
          </div>
          ))}
        </div>
      </div>
    </section>
  );
}