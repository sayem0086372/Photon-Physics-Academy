/**
 * @license
 * SPDX-License-Identifier: Apache-2.0
 */

import React, { useState, useEffect } from 'react';
import { Navbar } from './components/Navbar';
import { Hero } from './components/Hero';
import { About } from './components/About';
import { Programs } from './components/Programs';
import { Gallery } from './components/Gallery';
import { StoreBanner } from './components/StoreBanner';
import { BookStore } from './components/BookStore';
import { NoticeBoard } from './components/NoticeBoard';
import { Results } from './components/Results';
import { Contact } from './components/Contact';
import { Footer } from './components/Footer';
import { AdminGate } from './components/admin/AdminGate';

const isStorePath = () => /^\/(store|cart|checkout|order-confirmation)(\/.*)?$/.test(window.location.pathname);

export default function App() {
  const [currentView, setCurrentView] = useState<'public' | 'admin'>('public');
  const [isStore, setIsStore] = useState(isStorePath);
  const [inquiryProgram, setInquiryProgram] = useState<string>('');

  useEffect(() => {
    const syncLocation = () => {
      setIsStore(isStorePath());
      if (window.location.hash === '#admin') setCurrentView('admin');
      else setCurrentView('public');
    };
    syncLocation();
    window.addEventListener('hashchange', syncLocation);
    window.addEventListener('popstate', syncLocation);
    return () => {
      window.removeEventListener('hashchange', syncLocation);
      window.removeEventListener('popstate', syncLocation);
    };
  }, []);

  const handleSetView = (view: 'public' | 'admin') => {
    setCurrentView(view);
    if (view === 'admin') window.location.hash = 'admin';
    else {
      window.location.hash = 'home';
      if (isStorePath()) window.location.href = '/';
    }
  };

  const handleOpenInquiry = (programOrBook?: string) => {
    if (programOrBook) setInquiryProgram(programOrBook);
    if (isStorePath()) {
      window.location.href = '/#contact';
      return;
    }
    document.getElementById('contact')?.scrollIntoView({ behavior: 'smooth' });
  };

  if (currentView === 'admin') return <AdminGate onExitAdmin={() => handleSetView('public')} />;

  return (
    <div className="min-h-screen bg-white text-slate-900 flex flex-col font-sans selection:bg-blue-500/20 selection:text-blue-900">
      <Navbar onOpenInquiry={() => handleOpenInquiry()} onOpenAdmin={() => handleSetView('admin')} />
      <main className="flex-1">
        {isStore ? (
          <>
            <div className="bg-blue-900 text-white">
              <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 md:py-12">
                <p className="text-sm font-semibold tracking-wide text-sky-200">PHOTON PHYSICS ACADEMY</p>
                <h1 className="mt-2 text-3xl md:text-4xl font-black">Book Store</h1>
                <p className="mt-3 max-w-2xl text-sm md:text-base text-blue-100">Explore books and place your order with Cash on Delivery.</p>
                <a href="/" className="mt-5 inline-flex rounded-lg bg-white/10 hover:bg-white/20 border border-white/25 px-4 py-2 text-sm font-semibold transition">← Back to Home</a>
              </div>
            </div>
            <BookStore />
          </>
        ) : (
          <>
            <StoreBanner />
            <Hero onOpenInquiry={handleOpenInquiry} />
            <NoticeBoard />
            <About onOpenInquiry={() => handleOpenInquiry()} />
            <Programs onSelectProgram={handleOpenInquiry} />
            <Gallery />
            <Results onOpenInquiry={() => handleOpenInquiry()} />
            <Contact prefilledProgram={inquiryProgram} />
          </>
        )}
      </main>
      <Footer onOpenAdmin={() => handleSetView('admin')} />
    </div>
  );
}
