import React, { useState } from 'react';
import { CreditCard, Receipt, Search, CheckCircle, Clock, AlertCircle, MessageSquare, X, FileCheck } from 'lucide-react';
import { AdminPaymentRecord, AdminStudent } from '../../../types/admin';
import { EmptyState } from '../ui/EmptyState';
import { SectionToolbar } from '../ui/SectionToolbar';
import { StatusBadge } from '../ui/StatusBadge';

interface PaymentsTabProps {
  payments: AdminPaymentRecord[];
  students: AdminStudent[];
  onRecordPayment: (payment: Omit<AdminPaymentRecord, 'id'>) => Promise<AdminPaymentRecord | null> | AdminPaymentRecord | null;
  isRecordModalOpen: boolean;
  setIsRecordModalOpen: (open: boolean) => void;
}

export const PaymentsTab: React.FC<PaymentsTabProps> = ({
  payments,
  students,
  onRecordPayment,
  isRecordModalOpen,
  setIsRecordModalOpen,
}) => {
  const monthNames = ['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'];
  const currentDate = new Date();
  const currentYear = currentDate.getFullYear();
  const [selectedMonth, setSelectedMonth] = useState(monthNames[currentDate.getMonth()]);
  const [selectedYear, setSelectedYear] = useState(String(currentYear));
  const [statusFilter, setStatusFilter] = useState('All');
  const [searchTerm, setSearchTerm] = useState('');
  const [previewReceipt, setPreviewReceipt] = useState<AdminPaymentRecord | null>(null);
  const [messagePayment, setMessagePayment] = useState<{ payment: Omit<AdminPaymentRecord, 'id'>; student: AdminStudent } | null>(null);
  const [messageText, setMessageText] = useState('');
  const [sendToGuardian, setSendToGuardian] = useState(true);
  const [sendToStudent, setSendToStudent] = useState(false);
  const [sendingMessage, setSendingMessage] = useState(false);
  const [messageFeedback, setMessageFeedback] = useState('');
  const [messageFeedbackError, setMessageFeedbackError] = useState(false);
  const [savingPayment, setSavingPayment] = useState(false);
  const [pendingAttempt, setPendingAttempt] = useState<{ fingerprint: string; requestId: string } | null>(null);
  const [studentSearchTerm, setStudentSearchTerm] = useState('');

  // New payment form state
  const [newPay, setNewPay] = useState({
    studentId: students[0]?.id || '',
    billingMonth: monthNames[currentDate.getMonth()],
    billingYear: currentYear,
    payableAmount: 1500,
    paidAmount: 1500,
    dueAmount: 0,
    paymentStatus: 'Paid' as 'Paid' | 'Partial' | 'Due',
    paymentMethod: 'Cash' as 'Cash' | 'bKash' | 'Nagad',
    paymentDate: new Date().toISOString().split('T')[0],
  });

  const searchableStudents = students.filter((student) => {
    const query = studentSearchTerm.trim().toLowerCase();
    if (!query) return true;
    return [student.fullName, student.studentIdCode, student.classLevel, student.studentPhone, student.guardianPhone]
      .some((value) => String(value ?? '').toLowerCase().includes(query));
  });

  const getMonthlyPayment = (studentId: string, month: string, year: number) =>
    payments.find((item) => item.studentId === studentId && item.billingMonth === month && item.billingYear === year);

  const handleStudentSelect = (studentId: string) => {
    const student = students.find((s) => s.id === studentId);
    const fee = student ? student.monthlyFeeBdt : 1500;
    const existing = getMonthlyPayment(studentId, newPay.billingMonth, newPay.billingYear);
    const payable = existing?.payableAmount ?? fee;
    const alreadyPaid = existing?.monthlyPaidAmount ?? existing?.paidAmount ?? 0;
    const remaining = Math.max(0, payable - alreadyPaid);
    setNewPay({
      ...newPay,
      studentId,
      payableAmount: payable,
      paidAmount: remaining,
      dueAmount: remaining,
      paymentStatus: remaining === 0 ? 'Paid' : alreadyPaid > 0 ? 'Partial' : 'Due',
    });
  };

  const handleBillingMonthSelect = (month: string) => {
    const existing = getMonthlyPayment(newPay.studentId, month, newPay.billingYear);
    const student = students.find((s) => s.id === newPay.studentId);
    const payable = existing?.payableAmount ?? student?.monthlyFeeBdt ?? 1500;
    const alreadyPaid = existing?.monthlyPaidAmount ?? existing?.paidAmount ?? 0;
    const remaining = Math.max(0, payable - alreadyPaid);
    setNewPay({
      ...newPay,
      billingMonth: month,
      payableAmount: payable,
      paidAmount: remaining,
      dueAmount: remaining,
      paymentStatus: remaining === 0 ? 'Paid' : alreadyPaid > 0 ? 'Partial' : 'Due',
    });
  };

  const handlePaidAmountChange = (val: number) => {
    const payable = newPay.payableAmount;
    const existing = getMonthlyPayment(newPay.studentId, newPay.billingMonth, newPay.billingYear);
    const previouslyPaid = existing?.monthlyPaidAmount ?? existing?.paidAmount ?? 0;
    const totalPaid = Math.min(payable, previouslyPaid + Math.max(0, val));
    const due = Math.max(0, payable - totalPaid);
    let status: 'Paid' | 'Partial' | 'Due' = 'Paid';
    if (totalPaid === 0) status = 'Due';
    else if (due > 0) status = 'Partial';

    setNewPay({ ...newPay, paidAmount: Math.max(0, val), dueAmount: due, paymentStatus: status });
  };

  const handleSubmit = async (e: React.FormEvent) => {
    e.preventDefault();
    if (savingPayment) return;
    setSavingPayment(true);
    try {
      const student = students.find((s) => s.id === newPay.studentId);
      if (!student) {
        alert('অনুগ্রহ করে একজন শিক্ষার্থী নির্বাচন করুন।');
        return;
      }
      if (newPay.paidAmount <= 0) {
        alert('অনুগ্রহ করে ৳0-এর বেশি পেমেন্টের পরিমাণ লিখুন।');
        return;
      }

      const previousMonthly = payments.find((item) => item.studentId === student.id && item.billingMonth === newPay.billingMonth && item.billingYear === newPay.billingYear);
      const previousPaid = previousMonthly?.monthlyPaidAmount ?? previousMonthly?.paidAmount ?? 0;
      const projectedPaid = previousPaid + newPay.paidAmount;
      const willBeFullyPaid = projectedPaid >= newPay.payableAmount;
      const fingerprint = JSON.stringify({
        studentId: student.id,
        billingMonth: newPay.billingMonth,
        billingYear: newPay.billingYear,
        amount: newPay.paidAmount,
        method: newPay.paymentMethod,
        date: newPay.paymentDate,
      });
      // If the previous request timed out after committing, retry with the same
      // request UUID so the database returns the original transaction.
      const requestId = pendingAttempt?.fingerprint === fingerprint
        ? pendingAttempt.requestId
        : crypto.randomUUID();
      setPendingAttempt({ fingerprint, requestId });

      const payment: Omit<AdminPaymentRecord, 'id'> = {
        studentId: student.id,
        studentName: student.fullName,
        studentIdCode: student.studentIdCode,
        classLevel: student.classLevel,
        billingMonth: newPay.billingMonth,
        billingYear: newPay.billingYear,
        payableAmount: newPay.payableAmount,
        paidAmount: newPay.paidAmount,
        monthlyPaidAmount: projectedPaid,
        dueAmount: Math.max(0, newPay.payableAmount - projectedPaid),
        paymentStatus: willBeFullyPaid ? 'Paid' : 'Partial',
        paymentMethod: newPay.paymentMethod,
        receiptNumber: '', // generated transactionally by Supabase
        paymentDate: newPay.paymentDate,
        requestId,
      };
      const saved = await onRecordPayment(payment);
      if (!saved) return;
      setPendingAttempt(null);
      setIsRecordModalOpen(false);

      // Offer SMS only when this collection completes the monthly fee.
      if (saved.paymentStatus === 'Paid' && saved.paidAmount > 0) {
        setMessagePayment({ payment: saved, student });
        setMessageText(`Dear Guardian, the monthly fee for ${student.fullName} for ${saved.billingMonth} ${saved.billingYear} has been received by Photon Physics Academy. Thank you.`);
        setSendToGuardian(Boolean(student.guardianPhone.trim()));
        setSendToStudent(false);
        setMessageFeedback('');
        setMessageFeedbackError(false);
      }
    } finally {
      setSavingPayment(false);
    }
  };

  const sendPaymentMessage = async () => {
    if (!messagePayment) return;
    const recipients = new Set<string>();
    if (sendToGuardian && messagePayment.student.guardianPhone.trim()) recipients.add(messagePayment.student.guardianPhone.trim());
    if (sendToStudent && messagePayment.student.studentPhone.trim()) recipients.add(messagePayment.student.studentPhone.trim());
    if (!recipients.size) {
      setMessageFeedback('No phone number selected or available. Add a student/guardian phone number or close this window.');
      setMessageFeedbackError(true);
      return;
    }
    if (!messageText.trim()) {
      setMessageFeedback('Please enter a message first.');
      setMessageFeedbackError(true);
      return;
    }
    if (!window.confirm(`Send this SMS to ${recipients.size} recipient(s)? SMS charges may apply.`)) return;
    const { supabase } = await import('../../../lib/supabase');
    if (!supabase) {
      setMessageFeedback('Supabase is not configured, so SMS cannot be sent.');
      setMessageFeedbackError(true);
      return;
    }
    setSendingMessage(true);
    setMessageFeedback('');
    setMessageFeedbackError(false);
    try {
      const { data, error } = await supabase.functions.invoke('sms-gateway', {
        body: { action: 'send', to: [...recipients], msg: messageText.trim() },
      });
      if (error) throw new Error(error.message);
      if (data?.error) throw new Error(data.error);
      setMessageFeedback(`SMS gateway accepted ${data.sent ?? 0} message(s); ${data.failed ?? 0} failed. No message history was saved.`);
      setMessageFeedbackError(Boolean(data.failed));
    } catch (error) {
      setMessageFeedback(error instanceof Error ? error.message : 'Could not send SMS. Check SMS gateway configuration.');
      setMessageFeedbackError(true);
    } finally {
      setSendingMessage(false);
    }
  };

  // Metrics
  const latestMonthlyMap = new Map<string, AdminPaymentRecord>();
  for (const payment of payments) {
    const key = `${payment.studentId}:${payment.billingYear}:${payment.billingMonth}`;
    if (!latestMonthlyMap.has(key)) latestMonthlyMap.set(key, payment);
  }
  const latestMonthlyRows = [...latestMonthlyMap.values()];
  const totalBilled = latestMonthlyRows.reduce((acc, p) => acc + p.payableAmount, 0);
  const totalCollected = payments.reduce((acc, p) => acc + p.paidAmount, 0);
  const totalDue = latestMonthlyRows.reduce((acc, p) => acc + p.dueAmount, 0);

  // Filtered payments
  const filteredPayments = payments.filter((p) => {
    const matchesMonth = selectedMonth === 'All' || p.billingMonth === selectedMonth;
    const matchesYear = selectedYear === 'All' || String(p.billingYear) === selectedYear;
    const matchesStatus = statusFilter === 'All' || p.paymentStatus === statusFilter;
    const matchesSearch =
      p.studentName.toLowerCase().includes(searchTerm.toLowerCase()) ||
      p.studentIdCode.toLowerCase().includes(searchTerm.toLowerCase()) ||
      p.receiptNumber.toLowerCase().includes(searchTerm.toLowerCase());
    return matchesMonth && matchesYear && matchesStatus && matchesSearch;
  });

  return (
    <div className="space-y-6">
      {/* 3 Metric Summary Banner */}
      <div className="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <div className="p-5 rounded-xl bg-white border border-slate-200">
          <div className="text-xs font-semibold text-slate-500 uppercase tracking-wider mb-1">
            Total Payable
          </div>
          <div className="text-2xl sm:text-3xl font-extrabold text-slate-900 tabular-nums">
            ৳{totalBilled.toLocaleString()}
          </div>
          <div className="mt-1 text-xs text-slate-400 font-bangla">সকল শিক্ষার্থীর সম্মিলিত বিল</div>
        </div>

        <div className="p-5 rounded-xl bg-white border border-slate-200">
          <div className="text-xs font-semibold text-slate-500 uppercase tracking-wider mb-1">
            Total Collected
          </div>
          <div className="text-2xl sm:text-3xl font-extrabold text-emerald-600 tabular-nums">
            ৳{totalCollected.toLocaleString()}
          </div>
        </div>

        <div className="p-5 rounded-xl bg-white border border-slate-200">
          <div className="text-xs font-semibold text-slate-500 uppercase tracking-wider mb-1">
            Outstanding Dues
          </div>
          <div className="text-2xl sm:text-3xl font-extrabold text-amber-600 tabular-nums">
            ৳{totalDue.toLocaleString()}
          </div>
        </div>
      </div>

      {/* Control Bar */}
      <div className="flex flex-col sm:flex-row items-stretch sm:items-center justify-between gap-4 bg-white p-4 rounded-xl border border-slate-200">
        {/* Search */}
        <div className="relative flex-1">
          <Search className="w-4 h-4 absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400" />
          <input
            type="text"
            placeholder="শিক্ষার্থীর নাম, আইডি কোড বা রসিদ নম্বর..."
            value={searchTerm}
            onChange={(e) => setSearchTerm(e.target.value)}
            className="w-full pl-9 pr-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs sm:text-sm text-slate-900 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-blue-500"
          />
        </div>

        {/* Filters */}
        <div className="flex flex-wrap items-center gap-2">
          <select
            value={selectedMonth}
            onChange={(e) => setSelectedMonth(e.target.value)}
            aria-label="Filter payments by month"
            className="px-3 py-2.5 rounded-xl border border-slate-200 bg-slate-50 text-xs font-medium text-slate-700 focus:outline-none"
          >
            <option value="All">All Months</option>
            {monthNames.map((month) => <option key={month} value={month}>{month}</option>)}
          </select>

          <select
            value={selectedYear}
            onChange={(e) => setSelectedYear(e.target.value)}
            aria-label="Filter payments by year"
            className="px-3 py-2.5 rounded-xl border border-slate-200 bg-slate-50 text-xs font-medium text-slate-700 focus:outline-none"
          >
            <option value="All">All Years</option>
            {Array.from({ length: Math.max(1, currentYear - 2019 + 2) }, (_, index) => currentYear + 1 - index)
              .filter((year) => year >= 2020)
              .map((year) => <option key={year} value={String(year)}>{year}</option>)}
          </select>

          <select
            value={statusFilter}
            onChange={(e) => setStatusFilter(e.target.value)}
            className="px-3 py-2.5 rounded-xl border border-slate-200 bg-slate-50 text-xs font-medium text-slate-700 focus:outline-none"
          >
            <option value="All">All Status</option>
            <option value="Paid">Paid</option>
            <option value="Partial">Partial</option>
            <option value="Due">Due</option>
          </select>

          <button
            onClick={() => setIsRecordModalOpen(true)}
            className="px-4 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-semibold text-xs flex items-center gap-1.5 shadow-sm transition-colors whitespace-nowrap"
          >
            <Receipt className="w-4 h-4" />
            <span>Record Payment</span>
          </button>
        </div>
      </div>

      {/* Ledger Table */}
      <div className="rounded-xl bg-white border border-slate-200 overflow-hidden">
        <div className="overflow-x-auto">
          <table className="w-full text-left text-xs">
            <thead className="bg-slate-50 border-b border-slate-200 text-slate-500 font-semibold">
              <tr>
                <th className="py-3.5 px-4">Receipt No.</th>
                <th className="py-3.5 px-4">Student</th>
                <th className="py-3.5 px-4">Month</th>
                <th className="py-3.5 px-4 text-right">Payable</th>
                <th className="py-3.5 px-4 text-right">Paid</th>
                <th className="py-3.5 px-4 text-right">Due</th>
                <th className="py-3.5 px-4">Method</th>
                <th className="py-3.5 px-4">Status</th>
                <th className="py-3.5 px-4 text-center">Slip</th>
              </tr>
            </thead>
            <tbody className="divide-y divide-slate-100">
              {filteredPayments.length > 0 ? (
                filteredPayments.map((p) => (
                  <tr key={p.id} className="hover:bg-slate-50/80 transition-colors">
                    <td className="py-3 px-4 font-mono font-medium text-slate-700">
                      {p.receiptNumber}
                    </td>
                    <td className="py-3 px-4">
                      <div className="font-bold text-slate-900">{p.studentName}</div>
                      <div className="text-[10px] text-slate-400 font-mono">
                        {p.studentIdCode} · {p.classLevel}
                      </div>
                    </td>
                    <td className="py-3 px-4 text-slate-700">
                      {p.billingMonth} {p.billingYear}
                    </td>
                    <td className="py-3 px-4 text-right font-mono tabular-nums text-slate-700">
                      ৳{p.payableAmount.toLocaleString()}
                    </td>
                    <td className="py-3 px-4 text-right font-mono font-bold text-emerald-700 tabular-nums">
                      ৳{p.paidAmount.toLocaleString()}
                    </td>
                    <td className="py-3 px-4 text-right font-mono font-bold text-amber-700 tabular-nums">
                      ৳{p.dueAmount.toLocaleString()}
                    </td>
                    <td className="py-3 px-4">
                      <span className="px-2 py-0.5 rounded bg-slate-100 text-[11px] font-mono font-medium">
                        {p.paymentMethod}
                      </span>
                    </td>
                    <td className="py-3 px-4">
                      <span
                        className={`inline-block px-2.5 py-0.5 rounded-md text-[10px] font-semibold ${
                          p.paymentStatus === 'Paid'
                            ? 'bg-emerald-50 text-emerald-700 border border-emerald-200'
                            : p.paymentStatus === 'Partial'
                            ? 'bg-amber-50 text-amber-700 border border-amber-200'
                            : 'bg-rose-50 text-rose-700 border border-rose-200'
                        }`}
                      >
                        {p.paymentStatus}
                      </span>
                    </td>
                    <td className="py-3 px-4 text-center">
                      <button
                        onClick={() => setPreviewReceipt(p)}
                        className="px-2.5 py-1 text-[11px] font-semibold text-blue-700 hover:text-blue-800 bg-sky-50 hover:bg-sky-100 rounded-lg transition-colors border border-sky-200"
                      >
                        View
                      </button>
                    </td>
                  </tr>
                ))
              ) : (
                <tr>
                  <td colSpan={9}>
                    <EmptyState
                      icon={CreditCard}
                      title={payments.length === 0 ? 'No payments recorded' : 'No matching payments'}
                      description={
                        payments.length === 0
                          ? 'ফি গ্রহণ করলে লেনদেনের তালিকা এখানে দেখা যাবে।'
                          : 'অনুসন্ধান বা ফিল্টার পরিবর্তন করে আবার চেষ্টা করুন।'
                      }
                      action={
                        payments.length === 0
                          ? { label: 'Record Payment', onClick: () => setIsRecordModalOpen(true), icon: Receipt }
                          : undefined
                      }
                    />
                  </td>
                </tr>
              )}
            </tbody>
          </table>
        </div>
      </div>

      {/* Record Payment Modal */}
      {isRecordModalOpen && (
        <div className="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/60 backdrop-blur-sm p-4">
          <div className="relative max-w-md w-full bg-white rounded-xl border border-slate-200 shadow-2xl p-6">
            <button
              onClick={() => setIsRecordModalOpen(false)}
              className="absolute top-5 right-5 p-1.5 rounded-lg text-slate-400 hover:text-slate-800 bg-slate-100"
            >
              <X className="w-5 h-5" />
            </button>

            <h3 className="text-lg font-bold text-slate-900 font-bangla mb-1">
              Record Payment
            </h3>
            <p className="text-xs text-slate-500 mb-5">
              শিক্ষার্থীর ফি আদায় ও ডিজিটাল রসিদ সংরক্ষণ।
            </p>

            <form onSubmit={handleSubmit} className="space-y-4">
              <div>
                <label htmlFor="payment-student-search" className="block text-xs font-semibold text-slate-700 mb-1">
                  শিক্ষার্থী নির্বাচন করুন *
                </label>
                <div className="relative mb-2">
                  <Search className="w-4 h-4 absolute left-3 top-1/2 -translate-y-1/2 text-slate-400" />
                  <input
                    id="payment-student-search"
                    type="search"
                    value={studentSearchTerm}
                    onChange={(e) => {
                      const value = e.target.value;
                      setStudentSearchTerm(value);
                      const query = value.trim().toLowerCase();
                      const matches = students.filter((student) => !query ||
                        [student.fullName, student.studentIdCode, student.classLevel, student.studentPhone, student.guardianPhone]
                          .some((field) => String(field ?? '').toLowerCase().includes(query)));
                      if (matches.length > 0) handleStudentSelect(matches[0].id);
                      else setNewPay({ ...newPay, studentId: '' });
                    }}
                    placeholder="নাম, Student ID, class বা ফোন দিয়ে খুঁজুন..."
                    autoComplete="off"
                    className="w-full pl-9 pr-3 py-2.5 bg-white border border-slate-300 rounded-lg text-xs text-slate-900 placeholder:text-slate-400 focus:outline-none focus:ring-2 focus:ring-blue-500"
                  />
                </div>
                <select
                  value={newPay.studentId}
                  onChange={(e) => handleStudentSelect(e.target.value)}
                  className="w-full px-3 py-2.5 bg-slate-50 border border-slate-200 rounded-lg text-xs font-medium text-slate-900"
                  required
                >
                  {searchableStudents.length === 0 ? (
                    <option value="">কোনো শিক্ষার্থী পাওয়া যায়নি</option>
                  ) : searchableStudents.map((s) => (
                    <option key={s.id} value={s.id}>
                      {s.fullName} ({s.studentIdCode} - {s.classLevel})
                    </option>
                  ))}
                </select>
                {searchableStudents.length === 0 && (
                  <p className="mt-1 text-[11px] text-amber-700">Search text পরিবর্তন করে আবার চেষ্টা করুন।</p>
                )}
              </div>

              <div className="grid grid-cols-2 gap-3">
                <div>
                  <label className="block text-xs font-semibold text-slate-700 mb-1">
                    বিলিং মাস
                  </label>
                  <select
                    value={newPay.billingMonth}
                    onChange={(e) => handleBillingMonthSelect(e.target.value)}
                    className="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-lg text-xs text-slate-900"
                  >
                    {monthNames.map((month) => <option key={month} value={month}>{month}</option>)}
                  </select>
                </div>

                <div>
                  <label className="block text-xs font-semibold text-slate-700 mb-1">
                    বিলিং বছর
                  </label>
                  <select
                    value={newPay.billingYear}
                    onChange={(e) => {
                      const year = Number(e.target.value);
                      const existing = getMonthlyPayment(newPay.studentId, newPay.billingMonth, year);
                      const student = students.find((s) => s.id === newPay.studentId);
                      const payable = existing?.payableAmount ?? student?.monthlyFeeBdt ?? 1500;
                      const alreadyPaid = existing?.monthlyPaidAmount ?? existing?.paidAmount ?? 0;
                      const remaining = Math.max(0, payable - alreadyPaid);
                      setNewPay({ ...newPay, billingYear: year, payableAmount: payable, paidAmount: remaining, dueAmount: remaining, paymentStatus: remaining === 0 ? 'Paid' : alreadyPaid > 0 ? 'Partial' : 'Due' });
                    }}
                    className="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-lg text-xs text-slate-900"
                  >
                    {Array.from({ length: Math.max(1, currentYear - 2019 + 2) }, (_, index) => currentYear + 1 - index)
                      .filter((year) => year >= 2020)
                      .map((year) => <option key={year} value={year}>{year}</option>)}
                  </select>
                </div>

                <div>
                  <label className="block text-xs font-semibold text-slate-700 mb-1">
                    পেমেন্ট পদ্ধতি
                  </label>
                  <select
                    value={newPay.paymentMethod}
                    onChange={(e) =>
                      setNewPay({
                        ...newPay,
                        paymentMethod: e.target.value as 'Cash' | 'bKash' | 'Nagad',
                      })
                    }
                    className="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-lg text-xs text-slate-900"
                  >
                    <option value="Cash">Cash</option>
                    <option value="bKash">bKash</option>
                    <option value="Nagad">Nagad</option>
                  </select>
                </div>
              </div>

              <div className="grid grid-cols-3 gap-2">
                <div>
                  <label className="block text-xs font-semibold text-slate-700 mb-1">
                    প্রদেয় ফি (৳)
                  </label>
                  <input
                    type="number"
                    value={newPay.payableAmount}
                    onChange={(e) => setNewPay({ ...newPay, payableAmount: Number(e.target.value) })}
                    className="w-full px-2.5 py-2 bg-slate-50 border border-slate-200 rounded-lg text-xs font-mono font-bold text-slate-800"
                  />
                </div>

                <div>
                  <label className="block text-xs font-semibold text-slate-700 mb-1">
                    আদায়কৃত (৳)
                  </label>
                  <input
                    type="number"
                    value={newPay.paidAmount}
                    onChange={(e) => handlePaidAmountChange(Number(e.target.value))}
                    className="w-full px-2.5 py-2 bg-slate-50 border border-slate-200 rounded-lg text-xs font-mono font-bold text-emerald-700"
                    required
                  />
                </div>

                <div>
                  <label className="block text-xs font-semibold text-slate-700 mb-1">
                    বকেয়া (৳)
                  </label>
                  <input
                    type="number"
                    value={newPay.dueAmount}
                    readOnly
                    className="w-full px-2.5 py-2 bg-slate-100 border border-slate-200 rounded-lg text-xs font-mono font-bold text-amber-700"
                  />
                </div>
              </div>

              <p className="text-[11px] text-slate-500">A unique receipt number is generated automatically after Supabase confirms the payment.</p>

              <div className="pt-4 border-t border-slate-100 flex items-center justify-end gap-3">
                <button
                  type="button"
                  onClick={() => setIsRecordModalOpen(false)}
                  className="px-4 py-2 text-xs font-medium text-slate-600 hover:text-slate-900 bg-slate-100 rounded-lg"
                >
                  Cancel
                </button>
                <button
                  type="submit"
                  disabled={savingPayment}
                  aria-busy={savingPayment}
                  className="px-5 py-2 text-xs font-bold text-white bg-emerald-600 hover:bg-emerald-700 rounded-lg shadow-sm disabled:opacity-60 disabled:cursor-not-allowed"
                >
                  {savingPayment ? 'Saving Payment…' : 'Confirm Payment'}
                </button>
              </div>
            </form>
          </div>
        </div>
      )}

      {/* Optional payment confirmation SMS */}
      {messagePayment && (
        <div className="fixed inset-0 z-[60] flex items-center justify-center bg-slate-900/60 backdrop-blur-sm p-4">
          <div className="relative w-full max-w-lg rounded-2xl border border-slate-200 bg-white p-6 shadow-2xl">
            <button onClick={() => setMessagePayment(null)} aria-label="Close message dialog" className="absolute right-4 top-4 rounded-lg bg-slate-100 p-1.5 text-slate-500 hover:text-slate-900"><X className="h-5 w-5" /></button>
            <div className="mb-4 flex items-center gap-3">
              <div className="rounded-xl bg-blue-50 p-3 text-blue-700"><MessageSquare className="h-6 w-6" /></div>
              <div><h3 className="text-lg font-bold text-slate-900">Send Message</h3><p className="mt-0.5 text-xs text-slate-500">Monthly fee payment confirmation · SMS is optional</p></div>
            </div>
            <div className="mb-4 rounded-xl border border-emerald-100 bg-emerald-50 p-3 text-sm text-emerald-900">
              <div className="font-bold">{messagePayment.student.fullName}</div>
              <div>{messagePayment.payment.billingMonth} {messagePayment.payment.billingYear} · ৳{messagePayment.payment.paidAmount.toLocaleString()} received</div>
            </div>
            <div className="mb-4 grid grid-cols-1 gap-2 sm:grid-cols-2">
              <label className="flex items-center gap-2 rounded-lg border border-slate-200 p-3 text-sm"><input type="checkbox" checked={sendToGuardian} onChange={e => setSendToGuardian(e.target.checked)} disabled={!messagePayment.student.guardianPhone.trim()} /> Guardian {messagePayment.student.guardianPhone.trim() ? `(${messagePayment.student.guardianPhone})` : '(no phone number)'}</label>
              <label className="flex items-center gap-2 rounded-lg border border-slate-200 p-3 text-sm"><input type="checkbox" checked={sendToStudent} onChange={e => setSendToStudent(e.target.checked)} disabled={!messagePayment.student.studentPhone.trim()} /> Student {messagePayment.student.studentPhone.trim() ? `(${messagePayment.student.studentPhone})` : '(no phone number)'}</label>
            </div>
            <label className="block text-sm font-semibold text-slate-700">Message template <span className="font-normal text-slate-400">(editable)</span>
              <textarea value={messageText} onChange={e => setMessageText(e.target.value)} rows={4} maxLength={1000} className="mt-1 block w-full rounded-xl border border-slate-200 bg-slate-50 p-3 text-sm font-normal" />
            </label>
            {messageFeedback && <div className={`mt-3 rounded-lg border p-3 text-sm ${messageFeedbackError ? 'border-red-200 bg-red-50 text-red-800' : 'border-emerald-200 bg-emerald-50 text-emerald-800'}`}>{messageFeedback}</div>}
            <p className="mt-2 text-xs text-slate-500">The SMS is sent only when you press Send SMS. SMS provider charges may apply. Message history is not stored.</p>
            <div className="mt-5 flex flex-wrap justify-end gap-2">
              <button onClick={() => setMessagePayment(null)} className="rounded-lg bg-slate-100 px-4 py-2.5 text-sm font-semibold text-slate-700">Close</button>
              <button onClick={sendPaymentMessage} disabled={sendingMessage} className="inline-flex items-center gap-2 rounded-lg bg-blue-600 px-4 py-2.5 text-sm font-bold text-white hover:bg-blue-700 disabled:opacity-50"><MessageSquare className="h-4 w-4" />{sendingMessage ? 'Sending…' : 'Send SMS'}</button>
            </div>
          </div>
        </div>
      )}

      {/* Receipt Preview Drawer */}
      {previewReceipt && (
        <div className="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/60 backdrop-blur-sm p-4">
          <div className="relative max-w-md w-full bg-white rounded-xl border border-slate-200 shadow-2xl p-6">
            <button
              onClick={() => setPreviewReceipt(null)}
              className="absolute top-5 right-5 p-1.5 rounded-lg text-slate-400 hover:text-slate-800 bg-slate-100"
            >
              <X className="w-5 h-5" />
            </button>

            <div className="text-center pb-4 border-b border-slate-100">
              <div className="text-xs uppercase font-extrabold tracking-widest text-blue-700">
                PHOTON PHYSICS ACADEMY
              </div>
              <div className="text-xs text-slate-500 font-bangla mt-0.5">
                ১০, জিলা স্কুল রোড (বিপরীত গলি), ময়মনসিংহ
              </div>
              <div className="mt-2 inline-block px-3 py-1 rounded bg-sky-50 text-blue-900 font-mono text-xs font-bold border border-sky-100">
                মানি রসিদ: {previewReceipt.receiptNumber}
              </div>
            </div>

            <div className="py-4 space-y-2 text-xs">
              <div className="flex justify-between">
                <span className="text-slate-500">শিক্ষার্থীর নাম:</span>
                <span className="font-bold text-slate-900">{previewReceipt.studentName}</span>
              </div>
              <div className="flex justify-between">
                <span className="text-slate-500">আইডি ও শ্রেণি:</span>
                <span className="font-mono text-slate-800">
                  {previewReceipt.studentIdCode} ({previewReceipt.classLevel})
                </span>
              </div>
              <div className="flex justify-between">
                <span className="text-slate-500">মাস ও বছর:</span>
                <span className="text-slate-800">{previewReceipt.billingMonth} {previewReceipt.billingYear}</span>
              </div>
              <div className="flex justify-between">
                <span className="text-slate-500">পরিশোধের মাধ্যম:</span>
                <span className="font-mono text-slate-800">{previewReceipt.paymentMethod}</span>
              </div>
              <div className="flex justify-between">
                <span className="text-slate-500">তারিখ:</span>
                <span className="font-mono text-slate-800">{previewReceipt.paymentDate}</span>
              </div>

              <div className="pt-2 border-t border-slate-100 flex justify-between font-bold text-sm">
                <span>আদায়কৃত ফি:</span>
                <span className="font-mono text-emerald-700">৳{previewReceipt.paidAmount.toLocaleString()}</span>
              </div>
              {previewReceipt.dueAmount > 0 && (
                <div className="flex justify-between text-xs text-amber-700 font-semibold">
                  <span>অবশিষ্ট বকেয়া:</span>
                  <span className="font-mono">৳{previewReceipt.dueAmount.toLocaleString()}</span>
                </div>
              )}
            </div>

            {/* SMS Preview Strip */}
            <div className="mt-3 p-3 rounded-xl bg-slate-50 border border-slate-200 text-[11px] text-slate-600 font-bangla">
              <div className="flex items-center gap-1.5 font-bold text-slate-800 mb-1">
                <MessageSquare className="w-3.5 h-3.5 text-blue-600" />
                <span>এসএমএস নোটিফিকেশনের নমুনা:</span>
              </div>
              "Photon Physics Academy: {previewReceipt.studentName}-এর {previewReceipt.billingMonth} মাসের ফি ৳{previewReceipt.paidAmount} প্রাপ্ত হয়েছে। ধন্যবাদ।"
            </div>

            <div className="mt-4 pt-3 border-t border-slate-100 flex justify-end">
              <button
                onClick={() => setPreviewReceipt(null)}
                className="px-4 py-2 text-xs font-semibold text-white bg-blue-600 hover:bg-blue-700 rounded-lg"
              >
                Close
              </button>
            </div>
          </div>
        </div>
      )}
    </div>
  );
};
