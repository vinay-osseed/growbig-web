import ReactCountryFlag from "react-country-flag";
import eosLogo from "../assets/eos-logo.png"; // Change the path if needed

const partners = [
  {
    name: "United States",
    countryCode: "US",
    type: "country",
  },
  {
    name: "EOS Globe",
    type: "company",
    logo: eosLogo,
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
          Our Trusted Partners
        </h2>

        {/* Description */}
        <p className="mx-auto mt-7 max-w-[760px] text-center text-[24px] leading-[42px] text-[#64748B]">
          We proudly collaborate with trusted global partners to deliver
          innovative technology solutions and world-class services.
        </p>

        {/* Partner Cards */}
        <div className="mt-20 flex flex-wrap justify-center gap-8">
          {partners.map((partner, index) => (
            <div
              key={index}
              className="flex w-full max-w-md items-center gap-5 rounded-3xl border border-[#E5E7EB] bg-white p-6 shadow-[0_15px_40px_rgba(15,23,42,0.08)] transition-all duration-300 hover:-translate-y-1 hover:shadow-[0_20px_50px_rgba(15,23,42,0.12)]"
            >
              {/* Icon */}
              <div className="flex h-20 w-20 items-center justify-center rounded-full border-2 border-[#E5E7EB] bg-white shadow-sm overflow-hidden">
                {partner.type === "country" ? (
                  <ReactCountryFlag
                    countryCode={partner.countryCode}
                    svg
                    style={{
                      width: "38px",
                      height: "38px",
                      borderRadius: "50%",
                    }}
                  />
                ) : (
                  <img
                    src={partner.logo}
                    alt={partner.name}
                     className="h-full w-full object-contain p-2"
                  />
                )}
              </div>

              {/* Info */}
              <div>
                <h3 className="text-[24px] font-bold text-[#0B1F4D]">
                  {partner.name}
                </h3>
                <p className="mt-1 text-[15px] text-[#64748B]">
                  {partner.type === "country"
                    ? "Trusted Global Partner"
                    : "Technology Partner"}
                </p>
              </div>
            </div>
          ))}
        </div>
      </div>
    </section>
  );
}