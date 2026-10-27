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
const isAdminPath = () => window.location.pathname === '/admin' || window.location.pathname.startsWith('/admin/');

export default function App() {
  const [currentView, setCurrentView] = useState<'public' | 'admin'>('public');
  const [isStore, setIsStore] = useState(isStorePath);
  const [inquiryProgram, setInquiryProgram] = useState<string>('');

  useEffect(() => {
    const syncLocation = () => {
      setIsStore(isStorePath());
      if (isAdminPath() || window.location.hash === '#admin') setCurrentView('admin');
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
    if (view === 'admin') {
      if (!isAdminPath()) window.history.pushState({}, '', '/admin');
    } else {
      if (isAdminPath() || window.location.hash === '#admin') window.history.pushState({}, '', '/');
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
            <section aria-label="Our legacy" className="bg-white py-8 sm:py-10 border-y border-slate-200">
              <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 grid grid-cols-1 sm:grid-cols-2 gap-5 sm:gap-8">
                <div className="rounded-xl border border-slate-200 bg-slate-50 px-6 py-5 text-center">
                  <div className="text-3xl sm:text-4xl font-extrabold tracking-tight text-blue-800">23 Years</div>
                  <p className="mt-2 text-base sm:text-lg font-semibold text-slate-700 font-bangla">শিক্ষার ক্ষেত্রে আমাদের ২৩ বছরের ঐতিহ্য</p>
                </div>
                <div className="rounded-xl border border-slate-200 bg-slate-50 px-6 py-5 text-center">
                  <div className="text-3xl sm:text-4xl font-extrabold tracking-tight text-blue-800">11,000+</div>
                  <p className="mt-2 text-base sm:text-lg font-semibold text-slate-700 font-bangla">শিক্ষার্থী আমাদের কাছ থেকে পড়াশোনা করেছে</p>
                </div>
              </div>
            </section>
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
