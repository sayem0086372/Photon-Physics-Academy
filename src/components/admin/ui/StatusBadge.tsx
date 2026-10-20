import React from 'react';

const STYLES: Record<string, string> = {
  Paid: 'bg-emerald-50 text-emerald-700 border-emerald-200',
  Partial: 'bg-amber-50 text-amber-700 border-amber-200',
  Due: 'bg-rose-50 text-rose-700 border-rose-200',
  Active: 'bg-emerald-50 text-emerald-700 border-emerald-200',
  Draft: 'bg-slate-100 text-slate-600 border-slate-200',
  Upcoming: 'bg-sky-50 text-sky-700 border-sky-200',
  Full: 'bg-rose-50 text-rose-700 border-rose-200',
};

export const StatusBadge: React.FC<{ status: string; label?: string }> = ({ status, label }) => (
  <span
    className={`inline-block px-2 py-0.5 rounded-md text-[11px] font-semibold border ${
      STYLES[status] ?? 'bg-slate-100 text-slate-600 border-slate-200'
    }`}
  >
    {label ?? status}
  </span>
);
