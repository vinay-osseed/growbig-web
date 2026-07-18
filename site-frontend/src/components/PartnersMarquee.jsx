// const partners = [
//   {
//     name: "US",
//     bg: "bg-[#F9A826]",
//     text: "US",
//   },
  

// ];

// export default function PartnersMarquee() {
//   return (
//     <section className="bg-[#FAFAFA] py-28 overflow-hidden">
//       <div className="max-w-7xl mx-auto px-6">

//         {/* Badge */}
//         <div className="flex justify-center">
//           <span className="rounded-full border border-[#FDBA74] bg-[#FFF7ED] px-6 py-2 text-[15px] font-semibold tracking-[2px] uppercase text-[#F97316]">
//             Ecosystem
//           </span>
//         </div>

//         {/* Heading */}
//         <h2 className="mt-6 text-center text-[45px] font-extrabold leading-none tracking-[-2px] text-[#0B1F4D]">
//           Our Trusted Partners
//         </h2>

//         {/* Description */}
//         <p className="mx-auto mt-7 max-w-[760px] text-center text-[24px] leading-[42px] text-[#64748B]">
//           We collaborate with industry-leading technology partners to
//           deliver best-in-class solutions.
//         </p>
//       </div>

//       {/* Partners */}
//     <div className="mt-24 flex justify-center">
//   {partners.map((partner, index) => (
//     <div
//       key={index}
//       className="flex h-[68px] w-[220px] items-center rounded-2xl border border-[#E5E7EB] bg-white px-5 shadow-[0_8px_24px_rgba(15,23,42,0.05)]"
//     >
//       <div
//         className={`flex h-10 w-10 shrink-0 items-center justify-center rounded-full ${partner.bg}`}
//       >
//         <span className="text-[13px] font-bold text-white">
//           {partner.text}
//         </span>
//       </div>

//       <span className="ml-3 text-[18px] font-semibold text-[#1E293B]">
//         {partner.name}
//       </span>
//     </div>
//   ))}
// </div>
      
//     </section>
//   );
// }


import ReactCountryFlag from "react-country-flag";

const partners = [
  {
    name: "United States",
    countryCode: "US",
  },
];

export default function PartnersMarquee() {
  return (
    <section className="overflow-hidden bg-[#FAFAFA] py-28">
      <div className="mx-auto max-w-7xl px-6">
        {/* Badge */}
        <div className="flex justify-center">
          <span className="rounded-full border border-[#FDBA74] bg-[#FFF7ED] px-6 py-2 text-[15px] font-semibold uppercase tracking-[2px] text-[#F97316]">
            Ecosystem
          </span>
        </div>

        {/* Heading */}
        <h2 className="mt-6 text-center text-[45px] font-extrabold leading-none tracking-[-2px] text-[#0B1F4D]">
          Our Trusted Partner
        </h2>

        {/* Description */}
        <p className="mx-auto mt-7 max-w-[760px] text-center text-[24px] leading-[42px] text-[#64748B]">
          We proudly collaborate with our trusted partner in the United States
          to deliver innovative technology solutions and world-class services.
        </p>

        {/* Partner Card */}
        <div className="mt-20 flex justify-center px-6">
          {partners.map((partner, index) => (
            <div
              key={index}
              className="flex w-full max-w-md items-center gap-5 rounded-3xl border border-[#E5E7EB] bg-white p-6 shadow-[0_15px_40px_rgba(15,23,42,0.08)] transition-all duration-300 hover:-translate-y-1 hover:shadow-[0_20px_50px_rgba(15,23,42,0.12)]"
            >
              {/* Flag Circle */}
              <div className="flex h-16 w-16 items-center justify-center rounded-full bg-white border-2 border-[#E5E7EB] shadow-sm">
                <ReactCountryFlag
                  countryCode={partner.countryCode}
                  svg
                  style={{
                    width: "38px",
                    height: "38px",
                    borderRadius: "50%",
                    objectFit: "cover",
                  }}
                />
              </div>

              {/* Partner Info */}
              <div>
                <h3 className="text-[24px] font-bold text-[#0B1F4D]">
                  {partner.name}
                </h3>
                <p className="mt-1 text-[15px] text-[#64748B]">
                  Trusted Global Partner
                </p>
              </div>
            </div>
          ))}
        </div>
      </div>
    </section>
  );
}