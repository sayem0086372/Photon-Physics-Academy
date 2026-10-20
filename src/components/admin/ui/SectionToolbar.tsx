import React from 'react';

interface SectionToolbarProps {
  title: string;
  description?: string;
  children?: React.ReactNode;
}

/** Page toolbar: English title, short Bengali description, actions on the right. */
export const SectionToolbar: React.FC<SectionToolbarProps> = ({ title, description, children }) => (
  <div className="flex flex-col sm:flex-row sm:items-center justify-between gap-3 bg-white p-4 rounded-xl border border-slate-200">
    <div>
      <h2 className="text-sm font-bold text-slate-900">{title}</h2>
      {description && <p className="text-xs text-slate-500 font-bangla mt-0.5">{description}</p>}
    </div>
    {children && <div className="flex flex-wrap items-center gap-2">{children}</div>}
  </div>
);
