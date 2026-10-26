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
  const iconSizes = {
    sm: 'w-10 h-10',
    md: 'w-14 h-14',
    lg: 'w-16 h-16',
    xl: 'w-24 h-24',
  };

  return (
    <div className={`flex items-center gap-3 ${className}`}>
      <img
        src="https://cdn.phototourl.com/member/2026-10-26-00b8c0d9-38d9-4d60-a377-dfd8928013de.png"
        alt="Photon Physics Academy"
        className={`${iconSizes[size]} shrink-0 object-contain mix-blend-multiply`}
        loading="eager"
      />

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
