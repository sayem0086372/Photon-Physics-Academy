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
    <div className={`public-site min-h-screen bg-white text-slate-900 flex flex-col font-sans selection:bg-blue-500/20 selection:text-blue-900`}>
      <Navbar onOpenInquiry={() => handleOpenInquiry()} onOpenAdmin={() => handleSetView('admin')} />
      <main className="flex-1">
        {isStore ? (
          <>
            <BookStore />
          </>
        ) : (
          <>
            <StoreBanner />
            <NoticeBoard />
            <Hero onOpenInquiry={handleOpenInquiry} />
            <About onOpenInquiry={() => handleOpenInquiry()} />
            <Programs onSelectProgram={handleOpenInquiry} />
            <Gallery />
            <Contact prefilledProgram={inquiryProgram} />
          </>
        )}
      </main>
      {!isStore && <Footer onOpenAdmin={() => handleSetView('admin')} />}
    </div>
  );
}
