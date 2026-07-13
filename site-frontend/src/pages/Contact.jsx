import ContactHeroSection from "../components/Contact/ContactHeroSection";
import ContactFormPanel from "../components/Contact/ContactFormPanel";
import ContactInfoPanel from "../components/Contact/ContactInfoPanel";

function Contact() {
  return (
    <>
      <ContactHeroSection />

      <section className="max-w-screen-2xl mx-auto px-6 lg:px-10 py-16">
        <div className="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">

          {/* Left - Contact Form */}
          <div className="lg:col-span-8">
            <ContactFormPanel />
          </div>

          {/* Right - Info Panel */}
          <div className="lg:col-span-4">
            <ContactInfoPanel />
          </div>

        </div>
      </section>
    </>
  );
}

export default Contact;