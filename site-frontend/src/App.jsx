import { BrowserRouter, Routes, Route } from 'react-router-dom'
import { Navigate } from 'react-router-dom'
import Navbar from './components/Navbar'
import Footer from './components/Footer'
import ScrollToTop from './components/ScrollToTop'
import Home from './pages/Home'
import Careers from './pages/Careers'
import About from './pages/About'
import Contact from './pages/Contact'


function App() {
  return (
    <BrowserRouter>
      <ScrollToTop />
      <Navbar />
      <Routes>
        <Route path="/" element={<Home />} />
        <Route path="/careers" element={<Careers/>}/>
        <Route path="/services" element={<Navigate to="/#services" replace />} />  
        <Route path="/about" element={<About />} />
        <Route path="/contact" element={<Contact />} />
   
        <Route path="*" element={<Navigate to="/" replace />} />
      </Routes>
      <Footer />
    </BrowserRouter>
  )
}

export default App