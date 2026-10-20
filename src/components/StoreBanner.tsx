import React from 'react';
import { ArrowUpRight, BookOpen } from 'lucide-react';

/**
 * Set VITE_STORE_URL at build time to point the button at another domain.
 * If unset, the same site's /store route is used.
 */
const storeUrl = (import.meta.env.VITE_STORE_URL || '/store').trim();

export const StoreBanner: React.FC = () => (
  <section id="books" aria-labelledby="store-banner-title" className="bg-white py-4 sm:py-5">
    <div className="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
      <div className="flex flex-col gap-4 rounded-lg border border-slate-200 bg-slate-50 px-4 py-4 sm:flex-row sm:items-center sm:justify-between sm:px-6">
        <div className="flex items-start gap-4">
          <div className="flex h-10 w-10 shrink-0 items-center justify-center rounded-md bg-blue-900 text-white">
            <BookOpen className="h-5 w-5" aria-hidden="true" />
          </div>
          <div>
            <p className="text-xs font-semibold tracking-wide text-blue-800">PHOTON PHYSICS ACADEMY</p>
            <h2 id="store-banner-title" className="mt-1 text-lg font-bold tracking-tight text-slate-900 sm:text-xl">
              Find Your Next Book
            </h2>
            <p className="mt-1 max-w-2xl text-sm leading-6 text-slate-600">
              Explore concept books and other learning resources, and order online.
            </p>
          </div>
        </div>
        <a
          href={storeUrl}
          className="inline-flex min-h-11 shrink-0 items-center justify-center gap-2 rounded-lg bg-blue-900 px-5 py-3 text-sm font-semibold text-white transition-colors hover:bg-blue-800 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-blue-700 focus-visible:ring-offset-2"
        >
          Browse & Buy Books <ArrowUpRight className="h-4 w-4" aria-hidden="true" />
        </a>
      </div>
    </div>
  </section>
);
