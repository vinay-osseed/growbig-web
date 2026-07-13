import ContactFormPanel from './ContactFormPanel'
import ContactInfoPanel from './ContactInfoPanel'

export default function ContactContentSection() {
  return (
    <section className="bg-slate-50 px-6 pb-20 pt-28 sm:pb-24 sm:pt-32 lg:px-8 lg:pt-32">
      <div className="mx-auto grid max-w-7xl gap-10 lg:grid-cols-[1.05fr_0.95fr] lg:items-start">
        <ContactFormPanel />
        <ContactInfoPanel />
      </div>
    </section>
  )
}