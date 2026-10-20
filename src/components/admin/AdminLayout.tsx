import React, { useEffect, useState } from 'react';
import type { Session } from '@supabase/supabase-js';
import { AdminTab, AdminStudent, AdminPaymentRecord, AdminNoticeRecord, AdminBatchRecord, AdminExamResult } from '../../types/admin';
import {
  INITIAL_STUDENTS,
  INITIAL_PAYMENTS,
  INITIAL_NOTICES,
  INITIAL_BATCHES,
  INITIAL_RESULTS,
} from '../../data/adminMockData';
import { isSupabaseConfigured } from '../../lib/supabase';
import * as db from '../../lib/db';
import { AdminSidebar } from './AdminSidebar';
import { AdminHeader } from './AdminHeader';
import { OverviewTab } from './tabs/OverviewTab';
import { StudentsTab } from './tabs/StudentsTab';
import { PaymentsTab } from './tabs/PaymentsTab';
import { NoticesTab } from './tabs/NoticesTab';
import { ProgramsTab } from './tabs/ProgramsTab';
import { ResultsTab } from './tabs/ResultsTab';
import { SmsTab } from './tabs/SmsTab';
import { SettingsTab } from './tabs/SettingsTab';
import { StoreManagementTab } from './tabs/StoreManagementTab';

interface AdminLayoutProps {
  onExitAdmin: () => void;
  session: Session | null;
}

export const AdminLayout: React.FC<AdminLayoutProps> = ({ onExitAdmin }) => {
  const [activeTab, setActiveTab] = useState<AdminTab>('overview');
  const [isMobileSidebarOpen, setIsMobileSidebarOpen] = useState(false);
  const [isAddStudentModalOpen, setIsAddStudentModalOpen] = useState(false);
  const [isRecordPaymentModalOpen, setIsRecordPaymentModalOpen] = useState(false);

  // Core administrative state
  const supabaseReady = isSupabaseConfigured();
  const [students, setStudents] = useState<AdminStudent[]>(supabaseReady ? [] : INITIAL_STUDENTS);
  const [payments, setPayments] = useState<AdminPaymentRecord[]>(supabaseReady ? [] : INITIAL_PAYMENTS);
  const [notices, setNotices] = useState<AdminNoticeRecord[]>(supabaseReady ? [] : INITIAL_NOTICES);
  const [batches, setBatches] = useState<AdminBatchRecord[]>(supabaseReady ? [] : INITIAL_BATCHES);
  const [results, setResults] = useState<AdminExamResult[]>(supabaseReady ? [] : INITIAL_RESULTS);
  const [loading, setLoading] = useState(supabaseReady);
  const [dbError, setDbError] = useState('');

  const fail = (e: unknown) => {
    const message = e instanceof Error ? e.message : (e as any)?.message ?? 'Database error';
    if (/row-level security policy/i.test(message)) {
      setDbError('Supabase blocked this database change because of a Row Level Security (RLS) policy. Run src/db/fix-v1.0.9-all-tables-rls.sql in Supabase SQL Editor, and verify the admin UUID in that file exactly matches the user under Supabase Dashboard → Authentication → Users. Then sign out and sign back in.');
      return;
    }
    setDbError(message);
  };

  const refresh = async () => {
    try {
      const d = await db.loadAll();
      setStudents(d.students);
      setPayments(d.payments);
      setNotices(d.notices);
      setBatches(d.batches);
      setResults(d.results);
      setDbError('');
    } catch (e) {
      fail(e);
    } finally {
      setLoading(false);
    }
  };

  useEffect(() => {
    if (supabaseReady) refresh();
  }, []);

  // Handlers — write to Supabase when configured, otherwise stay in local demo state
  const handleAddStudent = async (newStudent: Omit<AdminStudent, 'id' | 'joinedDate'>) => {
    try {
      const student: AdminStudent = supabaseReady
        ? await db.insertStudent(newStudent)
        : { ...newStudent, id: `std-${Date.now()}`, joinedDate: new Date().toISOString().split('T')[0] };
      setStudents((prev) => [student, ...prev]);
    } catch (e) { fail(e); }
  };

  const handleUpdateStudent = async (id: string, data: Omit<AdminStudent, 'id' | 'joinedDate'>) => {
    try {
      const updated: AdminStudent = supabaseReady
        ? await db.updateStudent(id, data)
        : { ...(students.find((s) => s.id === id) as AdminStudent), ...data };
      setStudents((prev) => prev.map((s) => (s.id === id ? { ...updated, paymentStatus: s.paymentStatus } : s)));
    } catch (e) { fail(e); }
  };

  const handleDeleteStudent = async (id: string) => {
    try {
      if (supabaseReady) await db.removeStudent(id);
      setStudents((prev) => prev.filter((s) => s.id !== id));
      setPayments((prev) => prev.filter((p) => p.studentId !== id));
      setResults((prev) => prev.filter((r) => students.find((s) => s.id === id)?.studentIdCode !== r.studentIdCode));
    } catch (e) { fail(e); }
  };

  const handleRecordPayment = async (newPay: Omit<AdminPaymentRecord, 'id'>): Promise<AdminPaymentRecord | null> => {
    try {
      const record: AdminPaymentRecord = supabaseReady
        ? await db.insertPayment(newPay)
        : {
            ...newPay,
            id: `pay-${crypto.randomUUID()}`,
            receiptNumber: `RCP-${newPay.billingYear}-${String(['January','February','March','April','May','June','July','August','September','October','November','December'].indexOf(newPay.billingMonth) + 1).padStart(2, '0')}-${crypto.randomUUID().replace(/-/g, '').slice(0, 16).toUpperCase()}`,
          };
      setPayments((prev) => [record, ...prev.filter((item) => item.id !== record.id)]);
      setStudents((prev) =>
        prev.map((s) => (s.id === record.studentId ? { ...s, paymentStatus: record.paymentStatus } : s))
      );
      return record;
    } catch (e) { fail(e); return null; }
  };

  const handleAddNotice = async (newNotice: Omit<AdminNoticeRecord, 'id'>) => {
    try {
      const notice: AdminNoticeRecord = supabaseReady
        ? await db.insertNotice(newNotice)
        : { ...newNotice, id: `ntc-${Date.now()}` };
      setNotices((prev) => [notice, ...prev]);
    } catch (e) { fail(e); }
  };

  const handleDeleteNotice = async (id: string) => {
    try {
      if (supabaseReady) await db.removeNotice(id);
      setNotices((prev) => prev.filter((n) => n.id !== id));
    } catch (e) { fail(e); }
  };

  const handleTogglePublishNotice = async (id: string) => {
    const current = notices.find((n) => n.id === id);
    if (!current) return;
    try {
      if (supabaseReady) await db.setNoticePublished(id, !current.isPublished);
      setNotices((prev) => prev.map((n) => (n.id === id ? { ...n, isPublished: !n.isPublished } : n)));
    } catch (e) { fail(e); }
  };

  const handleAddBatch = async (newBatch: Omit<AdminBatchRecord, 'id'>) => {
    try {
      const batch: AdminBatchRecord = supabaseReady
        ? await db.insertBatch(newBatch)
        : { ...newBatch, id: `batch-${Date.now()}` };
      setBatches((prev) => [...prev, batch]);
    } catch (e) { fail(e); }
  };

  const handleUpdateBatch = async (id: string, data: Omit<AdminBatchRecord, 'id'>) => {
    try {
      const updated: AdminBatchRecord = supabaseReady
        ? await db.updateBatch(id, data)
        : { ...data, id };
      setBatches((prev) => prev.map((b) => (b.id === id ? updated : b)));
    } catch (e) { fail(e); }
  };

  const handleDeleteBatch = async (id: string) => {
    try {
      if (supabaseReady) await db.removeBatch(id);
      setBatches((prev) => prev.filter((b) => b.id !== id));
      // Students in this batch stay enrolled in the academy, just unassigned
      setStudents((prev) => prev.map((s) => (s.batchId === id ? { ...s, batchId: null } : s)));
    } catch (e) { fail(e); }
  };

  const handleAddResult = async (newRes: Omit<AdminExamResult, 'id'>) => {
    try {
      let res: AdminExamResult;
      if (supabaseReady) {
        const student = students.find((s) => s.studentIdCode === newRes.studentIdCode);
        if (!student) throw new Error('Student not found for this result');
        res = await db.insertResult(newRes, student.id);
      } else {
        res = { ...newRes, id: `res-${Date.now()}` };
      }
      setResults((prev) => [res, ...prev]);
    } catch (e) { fail(e); }
  };

  const handleDeleteResult = async (id: string) => {
    try {
      if (supabaseReady) await db.removeResult(id);
      setResults((prev) => prev.filter((r) => r.id !== id));
    } catch (e) { fail(e); }
  };

  // Enrolled count is always computed from the students linked to each batch
  const batchesWithCounts = batches.map((b) => ({
    ...b,
    enrolledStudents: students.filter((s) => s.batchId === b.id && s.status === 'active').length,
  }));

  return (
    <div className="min-h-screen bg-slate-50 text-slate-800 flex font-sans">
      {/* Sidebar Navigation */}
      <AdminSidebar
        activeTab={activeTab}
        onTabChange={setActiveTab}
        isOpenMobile={isMobileSidebarOpen}
        onCloseMobile={() => setIsMobileSidebarOpen(false)}
        onExitAdmin={onExitAdmin}
      />

      {/* Main Administrative Viewport */}
      <div className="flex-1 flex flex-col min-w-0">
        <AdminHeader
          activeTab={activeTab}
          onOpenMobileSidebar={() => setIsMobileSidebarOpen(true)}
          onExitAdmin={onExitAdmin}
        />

        <main className="flex-1 p-4 sm:p-6 lg:p-8 max-w-7xl w-full mx-auto overflow-y-auto">
          {dbError && (
            <div className="mb-4 flex items-start justify-between gap-3 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800">
              <span><strong>Database error:</strong> {dbError}</span>
              <button onClick={() => setDbError('')} className="font-semibold hover:underline shrink-0">Dismiss</button>
            </div>
          )}
          {loading && <p className="mb-4 text-sm text-slate-500">Loading from Supabase…</p>}
          {activeTab === 'overview' && (
            <OverviewTab
              students={students}
              payments={payments}
              batches={batches}
              onNavigateTab={setActiveTab}
              onOpenAddStudent={() => {
                setActiveTab('students');
                setIsAddStudentModalOpen(true);
              }}
              onOpenRecordPayment={() => {
                setActiveTab('payments');
                setIsRecordPaymentModalOpen(true);
              }}
            />
          )}

          {activeTab === 'students' && (
            <StudentsTab
              students={students}
              batches={batchesWithCounts}
              onAddStudent={handleAddStudent}
              onUpdateStudent={handleUpdateStudent}
              onDeleteStudent={handleDeleteStudent}
              isAddModalOpen={isAddStudentModalOpen}
              setIsAddModalOpen={setIsAddStudentModalOpen}
            />
          )}

          {activeTab === 'payments' && (
            <PaymentsTab
              payments={payments}
              students={students}
              onRecordPayment={handleRecordPayment}
              isRecordModalOpen={isRecordPaymentModalOpen}
              setIsRecordModalOpen={setIsRecordPaymentModalOpen}
            />
          )}

          {activeTab === 'notices' && (
            <NoticesTab
              notices={notices}
              onAddNotice={handleAddNotice}
              onDeleteNotice={handleDeleteNotice}
              onTogglePublish={handleTogglePublishNotice}
            />
          )}

          {activeTab === 'programs' && (
            <ProgramsTab
              batches={batchesWithCounts}
              onAddBatch={handleAddBatch}
              onUpdateBatch={handleUpdateBatch}
              onDeleteBatch={handleDeleteBatch}
            />
          )}

          {activeTab === 'results' && (
            <ResultsTab
              results={results}
              students={students}
              onAddResult={handleAddResult}
              onDeleteResult={handleDeleteResult}
            />
          )}

          {activeTab === 'sms' && <SmsTab students={students} batches={batchesWithCounts} />}
          {activeTab === 'store' && <StoreManagementTab />}
          {activeTab === 'settings' && <SettingsTab />}

        </main>
      </div>
    </div>
  );
};
