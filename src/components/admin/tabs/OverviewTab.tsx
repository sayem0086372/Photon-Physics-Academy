import React from 'react';
import {
  Users,
  Wallet,
  AlertCircle,
  BookOpen,
  ArrowUpRight,
  UserPlus,
  Receipt,
  BellRing,
  Award,
  GraduationCap,
  CreditCard,
} from 'lucide-react';
import { AdminStudent, AdminPaymentRecord, AdminBatchRecord, AdminTab } from '../../../types/admin';
import { StatCard } from '../ui/StatCard';
import { StatusBadge } from '../ui/StatusBadge';
import { EmptyState } from '../ui/EmptyState';

interface OverviewTabProps {
  students: AdminStudent[];
  payments: AdminPaymentRecord[];
  batches: AdminBatchRecord[];
  onNavigateTab: (tab: AdminTab) => void;
  onOpenAddStudent: () => void;
  onOpenRecordPayment: () => void;
}

const CLASS_BN: Record<string, string> = { 'Class 8': 'অষ্টম', 'Class 9': 'নবম', 'Class 10': 'দশম' };

export const OverviewTab: React.FC<OverviewTabProps> = ({
  students,
  payments,
  batches,
  onNavigateTab,
  onOpenAddStudent,
  onOpenRecordPayment,
}) => {
  // Metrics
  const classCounts = (['Class 8', 'Class 9', 'Class 10'] as const).map((c) => ({
    cls: c,
    count: students.filter((s) => s.classLevel === c).length,
  }));

  const activeBatches = batches.filter((b) => b.status === 'active');
  const batchClasses = (['Class 8', 'Class 9', 'Class 10'] as const)
    .filter((c) => activeBatches.some((b) => b.classLevel === c))
    .map((c) => CLASS_BN[c]);

  const latestMonthlyMap = new Map<string, AdminPaymentRecord>();
  for (const payment of payments) {
    const key = `${payment.studentId}:${payment.billingYear}:${payment.billingMonth}`;
    if (!latestMonthlyMap.has(key)) latestMonthlyMap.set(key, payment);
  }
  const latestMonthlyRows = [...latestMonthlyMap.values()];
  const totalCollected = payments.reduce((acc, p) => acc + p.paidAmount, 0);
  const totalDue = latestMonthlyRows.reduce((acc, p) => acc + p.dueAmount, 0);
  const totalExpected = latestMonthlyRows.reduce((acc, p) => acc + p.payableAmount, 0);
  const collectionRate = totalExpected > 0 ? Math.round((totalCollected / totalExpected) * 100) : 0;

  const quickActions = [
    { label: 'Add Student', icon: UserPlus, onClick: onOpenAddStudent },
    { label: 'Record Payment', icon: Receipt, onClick: onOpenRecordPayment },
    { label: 'Publish Notice', icon: BellRing, onClick: () => onNavigateTab('notices') },
    { label: 'Add Exam Marks', icon: Award, onClick: () => onNavigateTab('results') },
  ];

  return (
    <div className="space-y-6">
      {/* KPI cards */}
      <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <StatCard
          label="Total Students"
          value={students.length}
          icon={Users}
          tone="blue"
          hint={
            <span className="flex items-center gap-1.5 flex-wrap">
              {classCounts.map((c, i) => (
                <React.Fragment key={c.cls}>
                  {i > 0 && <span>·</span>}
                  <span>
                    {CLASS_BN[c.cls]}: <strong>{c.count}</strong>
                  </span>
                </React.Fragment>
              ))}
            </span>
          }
        />
        <StatCard
          label="Collected"
          value={`৳${totalCollected.toLocaleString()}`}
          icon={Wallet}
          tone="emerald"
          hint={`আদায়ের হার ${collectionRate}%`}
        />
        <StatCard
          label="Pending Due"
          value={`৳${totalDue.toLocaleString()}`}
          icon={AlertCircle}
          tone="amber"
          hint="পরিশোধের অপেক্ষায় থাকা বকেয়া"
        />
        <StatCard
          label="Active Batches"
          value={activeBatches.length}
          icon={BookOpen}
          tone="sky"
          hint={
            batchClasses.length > 0
              ? `${batchClasses.join(', ')} শ্রেণির ব্যাচ চলমান`
              : 'এখনো কোনো সক্রিয় ব্যাচ নেই'
          }
        />
      </div>

      {/* Quick actions */}
      <div className="p-5 rounded-xl bg-white border border-slate-200">
        <h3 className="text-sm font-bold text-slate-900 mb-3">Quick Actions</h3>
        <div className="grid grid-cols-2 sm:grid-cols-4 gap-3">
          {quickActions.map(({ label, icon: Icon, onClick }) => (
            <button
              key={label}
              onClick={onClick}
              className="p-3 rounded-lg bg-slate-50 hover:bg-blue-50 border border-slate-200 hover:border-blue-200 text-slate-800 hover:text-blue-800 font-semibold text-xs flex items-center justify-center gap-2 transition-colors"
            >
              <Icon className="w-4 h-4 text-blue-600" />
              <span>{label}</span>
            </button>
          ))}
        </div>
      </div>

      {/* Recent activity */}
      <div className="grid grid-cols-1 lg:grid-cols-2 gap-6">
        {/* Recent students */}
        <div className="rounded-xl bg-white border border-slate-200 p-5">
          <div className="flex items-center justify-between mb-4">
            <div>
              <h3 className="text-sm font-bold text-slate-900">Recent Students</h3>
              <p className="text-xs text-slate-500 font-bangla">সর্বশেষ ভর্তি হওয়া শিক্ষার্থী</p>
            </div>
            <button
              onClick={() => onNavigateTab('students')}
              className="text-xs font-semibold text-blue-700 hover:text-blue-800 flex items-center gap-1"
            >
              <span>View all</span>
              <ArrowUpRight className="w-3.5 h-3.5" />
            </button>
          </div>

          {students.length === 0 ? (
            <EmptyState
              compact
              icon={GraduationCap}
              title="No students yet"
              description="নতুন শিক্ষার্থী ভর্তি করলে এখানে তালিকা দেখা যাবে।"
              action={{ label: 'Add Student', onClick: onOpenAddStudent, icon: UserPlus }}
            />
          ) : (
            <div className="overflow-x-auto">
              <table className="w-full text-left text-xs">
                <thead>
                  <tr className="text-slate-500 border-b border-slate-100">
                    <th className="pb-2 font-medium">ID</th>
                    <th className="pb-2 font-medium">Name</th>
                    <th className="pb-2 font-medium">Class</th>
                    <th className="pb-2 font-medium">Fee</th>
                  </tr>
                </thead>
                <tbody className="divide-y divide-slate-100">
                  {students.slice(0, 4).map((s) => (
                    <tr key={s.id}>
                      <td className="py-2.5 font-mono text-slate-600">{s.studentIdCode}</td>
                      <td className="py-2.5 font-medium text-slate-900">
                        <div>{s.fullName}</div>
                        {s.banglaName && <div className="text-[10px] text-slate-400 font-bangla">{s.banglaName}</div>}
                      </td>
                      <td className="py-2.5 text-slate-600">{s.classLevel}</td>
                      <td className="py-2.5">
                        <StatusBadge status={s.paymentStatus} />
                      </td>
                    </tr>
                  ))}
                </tbody>
              </table>
            </div>
          )}
        </div>

        {/* Recent payments */}
        <div className="rounded-xl bg-white border border-slate-200 p-5">
          <div className="flex items-center justify-between mb-4">
            <div>
              <h3 className="text-sm font-bold text-slate-900">Recent Payments</h3>
              <p className="text-xs text-slate-500 font-bangla">সাম্প্রতিক ফি লেনদেন</p>
            </div>
            <button
              onClick={() => onNavigateTab('payments')}
              className="text-xs font-semibold text-blue-700 hover:text-blue-800 flex items-center gap-1"
            >
              <span>View all</span>
              <ArrowUpRight className="w-3.5 h-3.5" />
            </button>
          </div>

          {payments.length === 0 ? (
            <EmptyState
              compact
              icon={CreditCard}
              title="No payments recorded"
              description="ফি গ্রহণ করলে লেনদেনের তালিকা এখানে দেখা যাবে।"
              action={{ label: 'Record Payment', onClick: onOpenRecordPayment, icon: Receipt }}
            />
          ) : (
            <div className="overflow-x-auto">
              <table className="w-full text-left text-xs">
                <thead>
                  <tr className="text-slate-500 border-b border-slate-100">
                    <th className="pb-2 font-medium">Receipt</th>
                    <th className="pb-2 font-medium">Student</th>
                    <th className="pb-2 font-medium">Method</th>
                    <th className="pb-2 font-medium text-right">Paid</th>
                  </tr>
                </thead>
                <tbody className="divide-y divide-slate-100">
                  {payments.slice(0, 4).map((p) => (
                    <tr key={p.id}>
                      <td className="py-2.5 font-mono text-slate-600">{p.receiptNumber}</td>
                      <td className="py-2.5 font-medium text-slate-900">{p.studentName}</td>
                      <td className="py-2.5">
                        <span className="px-1.5 py-0.5 rounded bg-slate-100 text-[10px] font-mono text-slate-600">
                          {p.paymentMethod}
                        </span>
                      </td>
                      <td className="py-2.5 text-right font-mono font-bold text-slate-900 tabular-nums">
                        ৳{p.paidAmount.toLocaleString()}
                      </td>
                    </tr>
                  ))}
                </tbody>
              </table>
            </div>
          )}
        </div>
      </div>
    </div>
  );
};
