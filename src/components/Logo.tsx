import React from 'react';

interface LogoProps {
  className?: string;
  showText?: boolean;
  size?: 'sm' | 'md' | 'lg' | 'xl';
}

export const Logo: React.FC<LogoProps> = ({
  className = '',
  showText = true,
  size = 'md',
}) => {
  // Dimensions
  const iconSizes = {
    sm: 'w-8 h-8',
    md: 'w-10 h-10',
    lg: 'w-14 h-14',
    xl: 'w-20 h-20',
  };

  return (
    <div className={`flex items-center gap-3 ${className}`}>
      {/* SVG Vector Emblem of Lightbulb with Graduation Cap & Atom Orbit */}
      <svg
        className={`${iconSizes[size]} shrink-0 transition-transform duration-200 group-hover:scale-105`}
        viewBox="0 0 200 220"
        fill="none"
        xmlns="http://www.w3.org/2000/svg"
        aria-label="Photon Physics Academy Official Emblem"
      >
        <defs>
          {/* Gradients */}
          <linearGradient id="bulbGlowGrad" x1="60" y1="60" x2="140" y2="140" gradientUnits="userSpaceOnUse">
            <stop offset="0%" stopColor="#0ea5e9" />
            <stop offset="60%" stopColor="#1e3a8a" />
            <stop offset="100%" stopColor="#0f172a" />
          </linearGradient>

          <linearGradient id="capGrad" x1="40" y1="30" x2="110" y2="90" gradientUnits="userSpaceOnUse">
            <stop offset="0%" stopColor="#38bdf8" />
            <stop offset="40%" stopColor="#2563eb" />
            <stop offset="100%" stopColor="#1d4ed8" />
          </linearGradient>

          <linearGradient id="rimGrad" x1="60" y1="60" x2="140" y2="140" gradientUnits="userSpaceOnUse">
            <stop offset="0%" stopColor="#94a3b8" />
            <stop offset="50%" stopColor="#475569" />
            <stop offset="100%" stopColor="#334155" />
          </linearGradient>

          <linearGradient id="threadGrad" x1="80" y1="160" x2="120" y2="190" gradientUnits="userSpaceOnUse">
            <stop offset="0%" stopColor="#cbd5e1" />
            <stop offset="50%" stopColor="#64748b" />
            <stop offset="100%" stopColor="#334155" />
          </linearGradient>
        </defs>

        {/* Outer Bulb Contour */}
        <path
          d="M66 112 C52 94 56 64 74 46 C92 28 122 30 138 48 C152 64 152 92 138 112 C131 122 127 132 127 142 L77 142 C77 132 73 122 66 112 Z"
          fill="url(#rimGrad)"
        />

        {/* Inner Luminous Sphere */}
        <circle cx="102" cy="85" r="42" fill="url(#bulbGlowGrad)" />

        {/* Atom Orbits & Nucleus */}
        <g stroke="#ffffff" strokeWidth="2.5" fill="none" opacity="0.95">
          {/* Ellipse Orbit 1 */}
          <ellipse cx="102" cy="85" rx="34" ry="12" transform="rotate(-30 102 85)" />
          {/* Ellipse Orbit 2 */}
          <ellipse cx="102" cy="85" rx="34" ry="12" transform="rotate(30 102 85)" />
          {/* Horizontal Orbit 3 */}
          <ellipse cx="102" cy="85" rx="34" ry="10" transform="rotate(90 102 85)" strokeDasharray="3 2" opacity="0.4" />
        </g>

        {/* Nucleus & Electron Nodes */}
        <circle cx="102" cy="85" r="5" fill="#ffffff" />
        {/* Electron particles on orbits */}
        <circle cx="80" cy="74" r="4" fill="#ffffff" />
        <circle cx="124" cy="96" r="4" fill="#ffffff" />
        <circle cx="118" cy="72" r="3.5" fill="#ffffff" />
        <circle cx="86" cy="98" r="3.5" fill="#ffffff" />

        {/* Bulb Neck & Screw Threads */}
        <g>
          {/* Thread Ridge 1 */}
          <rect x="75" y="146" width="54" height="10" rx="5" fill="url(#threadGrad)" />
          {/* Thread Ridge 2 */}
          <rect x="76" y="159" width="52" height="10" rx="5" fill="url(#threadGrad)" />
          {/* Thread Ridge 3 */}
          <rect x="80" y="172" width="44" height="9" rx="4.5" fill="url(#threadGrad)" />
          {/* Bottom Contact Tip */}
          <path d="M88 183 C88 183 94 196 102 196 C110 196 116 183 116 183 Z" fill="#1e293b" />
        </g>

        {/* Graduation Mortarboard Cap (tilted on top-left of bulb) */}
        <g transform="translate(18, 12)">
          {/* Cap Diamond Top */}
          <path
            d="M58 36 L100 12 L72 3 L30 27 Z"
            fill="url(#capGrad)"
            stroke="#1d4ed8"
            strokeWidth="1.5"
          />
          {/* Cap Lower Skull Band */}
          <path
            d="M48 29 L70 41 L88 31 L88 38 C88 47 70 51 60 48 L48 37 Z"
            fill="#1e40af"
          />
          {/* Tassel Button & Cord */}
          <circle cx="68" cy="22" r="2.5" fill="#ffffff" />
          <path
            d="M68 22 C60 25 50 34 46 44"
            stroke="#38bdf8"
            strokeWidth="2"
            fill="none"
          />
          {/* Tassel Fringe */}
          <path d="M46 44 L43 54 L48 53 Z" fill="#38bdf8" />
        </g>
      </svg>

      {/* Brand Text Block Matching Logo Typography */}
      {showText && (
        <div className="flex flex-col">
          <span className="text-base sm:text-lg font-black tracking-tight text-blue-900 leading-none">
            PHOTON <span className="text-sky-600">PHYSICS</span>
          </span>
          <span className="text-[10px] sm:text-[11px] font-bold tracking-[0.32em] text-slate-700 uppercase -mt-0.5">
            ACADEMY
          </span>
          <span className="text-[10px] font-bangla text-sky-800 -mt-0.5 sm:block hidden">
            পদার্থবিজ্ঞান একাডেমি · ময়মনসিংহ
          </span>
        </div>
      )}
    </div>
  );
};
