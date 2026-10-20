import React from 'react';
import type { LucideIcon } from 'lucide-react';

type Tone = 'blue' | 'emerald' | 'amber' | 'sky';

const TONES: Record<Tone, { chip: string; value: string }> = {
  blue: { chip: 'bg-blue-50 text-blue-700', value: 'text-slate-900' },
  emerald: { chip: 'bg-emerald-50 text-emerald-700', value: 'text-slate-900' },
  amber: { chip: 'bg-amber-50 text-amber-700', value: 'text-amber-600' },
  sky: { chip: 'bg-sky-50 text-sky-700', value: 'text-slate-900' },
};

interface StatCardProps {
  label: string;
  value: React.ReactNode;
  hint?: React.ReactNode;
  icon: LucideIcon;
  tone?: Tone;
}

export const StatCard: React.FC<StatCardProps> = ({ label, value, hint, icon: Icon, tone = 'blue' }) => (
  <div className="p-5 rounded-xl bg-white border border-slate-200">
    <div className="flex items-center justify-between mb-3">
      <span className="text-xs font-medium text-slate-500">{label}</span>
      <div className={`w-8 h-8 rounded-lg flex items-center justify-center ${TONES[tone].chip}`}>
        <Icon className="w-4 h-4" />
      </div>
    </div>
    <div className={`text-2xl font-bold tabular-nums ${TONES[tone].value}`}>{value}</div>
    {hint && <div className="mt-1.5 text-xs text-slate-500 font-bangla">{hint}</div>}
  </div>
);
