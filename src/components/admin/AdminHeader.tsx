import React from 'react';
import { Menu, ExternalLink } from 'lucide-react';
import { AdminTab } from '../../types/admin';
import { ADMIN_NAV } from './adminNav';

interface AdminHeaderProps {
  activeTab: AdminTab;
  onOpenMobileSidebar: () => void;
  onExitAdmin: () => void;
}

export const AdminHeader: React.FC<AdminHeaderProps> = ({
  activeTab,
  onOpenMobileSidebar,
  onExitAdmin,
}) => {
  const title = ADMIN_NAV.find((n) => n.id === activeTab)?.label ?? '';

  return (
    <header className="sticky top-0 z-20 h-16 bg-white border-b border-slate-200 px-4 sm:px-6 flex items-center justify-between">
      <div className="flex items-center gap-3">
        <button
          onClick={onOpenMobileSidebar}
          className="lg:hidden p-2 rounded-lg text-slate-600 hover:text-slate-900 hover:bg-slate-100"
          aria-label="Open Admin Menu"
        >
          <Menu className="w-5 h-5" />
        </button>
        <h1 className="text-base sm:text-lg font-bold text-slate-900">{title}</h1>
      </div>

      <div className="flex items-center gap-3">
        <div className="flex items-center gap-2 pl-3 border-l border-slate-200">
          <div className="w-8 h-8 rounded-lg bg-blue-100 flex items-center justify-center text-blue-700 font-bold text-xs">
            JS
          </div>
          <div className="hidden md:block text-left">
            <div className="text-xs font-bold text-slate-900 leading-none">Jewel Sir</div>
            <div className="text-[10px] text-slate-500 leading-none mt-1">Administrator</div>
          </div>
        </div>

        <button
          onClick={onExitAdmin}
          className="p-2 sm:px-3 sm:py-1.5 rounded-lg text-xs font-semibold text-slate-700 hover:text-blue-700 hover:bg-sky-50 border border-slate-200 transition-colors flex items-center gap-1.5"
          title="Go to Public Website"
        >
          <ExternalLink className="w-3.5 h-3.5" />
          <span className="hidden sm:inline">Public Site</span>
        </button>
      </div>
    </header>
  );
};
