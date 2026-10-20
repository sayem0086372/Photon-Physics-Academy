import {
  LayoutDashboard,
  Users,
  CreditCard,
  Bell,
  BookOpen,
  Award,
  MessageSquare,
  ShoppingBag,
  Settings,
  type LucideIcon,
} from 'lucide-react';
import { AdminTab } from '../../types/admin';

export interface AdminNavItem {
  id: AdminTab;
  label: string;
  icon: LucideIcon;
}

/** Single source of truth for sidebar items and header titles. */
export const ADMIN_NAV: AdminNavItem[] = [
  { id: 'overview', label: 'Dashboard Overview', icon: LayoutDashboard },
  { id: 'students', label: 'Student Management', icon: Users },
  { id: 'payments', label: 'Fees & Payments', icon: CreditCard },
  { id: 'notices', label: 'Notice Management', icon: Bell },
  { id: 'programs', label: 'Programs & Batches', icon: BookOpen },
  { id: 'results', label: 'Results & Exams', icon: Award },
  { id: 'sms', label: 'SMS Management', icon: MessageSquare },
  { id: 'store', label: 'Store Management', icon: ShoppingBag },
  { id: 'settings', label: 'Settings', icon: Settings },
];
