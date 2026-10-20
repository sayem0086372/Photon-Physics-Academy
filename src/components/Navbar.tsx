import React, { useState } from 'react';
import { Menu, X, Phone, MessageSquare, Shield } from 'lucide-react';
import { Logo } from './Logo';

interface NavbarProps {
  onOpenInquiry: (programOrBook?: string) => void;
  onOpenAdmin?: () => void;
}

export const Navbar: React.FC<NavbarProps> = ({ onOpenInquiry, onOpenAdmin }) => {
  const [mobileMenuOpen, setMobileMenuOpen] = useState(false);

  const navLinks = [
    { label: 'হোম', href: '/' },
    { label: 'আমাদের সম্পর্কে', href: '/#about' },
    { label: 'প্রোগ্রামসমূহ', href: '/#programs' },
    { label: 'ক্লাসরুম', href: '/#gallery' },
    { label: 'বুক স্টোর', href: '/store' },
    { label: 'নোটিশ বোর্ড', href: '/#notices' },
    { label: 'ফলাফল', href: '/#results' },
    { label: 'যোগাযোগ', href: '/#contact' },
  ];

  const handleNavClick = (href: string) => {
    setMobileMenuOpen(false);
    // Section links point back to the homepage; route links (for example /store) navigate normally.
    if (href.startsWith('/#') || href === '/') {
      window.location.href = href;
      return;
    }
    window.location.href = href;
  };

  return (
    <header className="sticky top-0 z-40 w-full border-b border-sky-100 bg-white/95 backdrop-blur-md shadow-xs">
      <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div className="flex items-center justify-between min-h-16 md:min-h-20 py-2 gap-3">
          {/* Zone 1: Official Logo */}
          <a
            href="/"
            className="flex items-center group whitespace-nowrap shrink-0 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-sky-500 rounded-lg p-1"
          >
            <Logo size="md" showText={true} />
          </a>

          {/* Zone 2: Navigation Links (Desktop) */}
          <nav className="hidden 2xl:flex items-center gap-3 text-xs font-medium text-slate-700 shrink min-w-0">
            {navLinks.map((link) => (
              <a
                key={link.href}
                href={link.href}
                className="hover:text-blue-700 transition-colors py-1 relative focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-sky-500 rounded whitespace-nowrap shrink-0"
              >
                {link.label}
              </a>
            ))}
          </nav>

          {/* Zone 3: Primary Action & Quick Phone */}
          <div className="hidden md:flex items-center gap-2 shrink-0">
            <a
              href="tel:+8801312364499"
              className="flex items-center gap-1.5 text-xs font-semibold text-blue-900 bg-sky-50 hover:bg-sky-100 border border-sky-200 px-3 py-2 rounded-lg transition-colors whitespace-nowrap"
              title="Call Academy Directly"
            >
              <Phone className="w-3.5 h-3.5 text-blue-600" />
              <span>01312-364499</span>
            </a>

            <button
              onClick={() => onOpenInquiry()}
              className="px-3 py-2 text-xs font-semibold text-white bg-gradient-to-r from-blue-600 via-sky-600 to-blue-700 hover:from-blue-700 hover:to-blue-800 rounded-lg shadow-sm shadow-blue-500/20 transition-all duration-200 whitespace-nowrap shrink-0 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-sky-500"
            >
              ভর্তি সম্পর্কে জানুন
            </button>

            {onOpenAdmin && (
              <button
                onClick={onOpenAdmin}
                className="px-3 py-2 text-xs font-semibold text-slate-700 hover:text-blue-700 bg-slate-100 hover:bg-sky-50 border border-slate-200 rounded-lg flex items-center gap-1.5 transition-colors whitespace-nowrap shrink-0"
                title="Open Admin Dashboard"
              >
                <Shield className="w-3.5 h-3.5 text-blue-600" />
                <span>Admin</span>
              </button>
            )}
          </div>

          {/* Mobile Hamburger Button */}
          <div className="flex md:hidden items-center gap-2">
            <a
              href="tel:+8801312364499"
              className="p-2 rounded-lg bg-sky-50 border border-sky-200 text-blue-700 hover:bg-sky-100"
              aria-label="Call Now"
            >
              <Phone className="w-4 h-4" />
            </a>
            {onOpenAdmin && (
              <button
                onClick={onOpenAdmin}
                className="p-2 rounded-lg bg-slate-100 border border-slate-200 text-slate-700 hover:text-blue-700 hover:bg-sky-50"
                aria-label="Admin Login"
                title="Admin Login"
              >
                <Shield className="w-4 h-4" />
              </button>
            )}
            <button
              onClick={() => setMobileMenuOpen(!mobileMenuOpen)}
              className="p-2 rounded-lg bg-slate-100 border border-slate-200 text-slate-700 hover:text-slate-900 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-sky-500"
              aria-expanded={mobileMenuOpen}
              aria-label={mobileMenuOpen ? 'মেনু বন্ধ করুন' : 'মেনু খুলুন'}
            >
              {mobileMenuOpen ? <X className="w-5 h-5" /> : <Menu className="w-5 h-5" />}
            </button>
          </div>
        </div>
      </div>

      {/* Mobile Navigation Drawer */}
      {mobileMenuOpen && (
        <div className="md:hidden border-b border-sky-100 bg-white px-4 pt-3 pb-6 shadow-xl">
          <div className="grid grid-cols-2 gap-2 mb-4">
            {navLinks.map((link) => (
              <button
                key={link.href}
                onClick={() => handleNavClick(link.href)}
                className="text-left py-2.5 px-3 rounded-lg text-sm font-medium text-slate-700 hover:text-blue-700 hover:bg-sky-50 transition-colors"
              >
                {link.label}
              </button>
            ))}
          </div>

          <div className="pt-3 border-t border-slate-100 flex flex-col gap-2.5">
            <button
              onClick={() => {
                setMobileMenuOpen(false);
                onOpenInquiry();
              }}
              className="w-full py-2.5 px-4 text-center text-sm font-semibold text-white bg-blue-600 hover:bg-blue-700 rounded-lg shadow-sm transition-colors flex items-center justify-center gap-2"
            >
              <MessageSquare className="w-4 h-4" />
              ভর্তি সম্পর্কে জানুন
            </button>

            <div className="grid grid-cols-2 gap-2 text-xs">
              <a
                href="tel:+8801312364499"
                className="flex items-center justify-center gap-1.5 py-2 px-2 bg-sky-50 hover:bg-sky-100 border border-sky-200 rounded-lg text-blue-900 font-medium"
              >
                <Phone className="w-3.5 h-3.5 text-blue-600" />
                <span>01312-364499</span>
              </a>
              <a
                href="tel:01722003995"
                className="flex items-center justify-center gap-1.5 py-2 px-2 bg-sky-50 hover:bg-sky-100 border border-sky-200 rounded-lg text-blue-900 font-medium"
              >
                <Phone className="w-3.5 h-3.5 text-blue-600" />
                <span>01722-003995</span>
              </a>
            </div>

            {onOpenAdmin && (
              <button
                onClick={() => {
                  setMobileMenuOpen(false);
                  onOpenAdmin();
                }}
                className="w-full py-2 px-3 rounded-lg text-xs font-semibold text-slate-700 bg-slate-100 hover:bg-slate-200 border border-slate-200 flex items-center justify-center gap-2 transition-colors mt-1"
              >
                <Shield className="w-3.5 h-3.5 text-blue-600" />
                <span>Admin Login</span>
              </button>
            )}
          </div>
        </div>
      )}
    </header>
  );
};
