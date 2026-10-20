import React, { useState } from 'react';
import { Plus, Clock, MapPin, User, CheckCircle2, X, Edit, Trash2, BookOpen } from 'lucide-react';
import { AdminBatchRecord, ClassLevel } from '../../../types/admin';
import { EmptyState } from '../ui/EmptyState';
import { SectionToolbar } from '../ui/SectionToolbar';
import { StatusBadge } from '../ui/StatusBadge';

interface ProgramsTabProps {
  batches: AdminBatchRecord[];
  onAddBatch: (batch: Omit<AdminBatchRecord, 'id'>) => void;
  onUpdateBatch: (id: string, batch: Omit<AdminBatchRecord, 'id'>) => void;
  onDeleteBatch: (id: string) => void;
}

export const ProgramsTab: React.FC<ProgramsTabProps> = ({ batches, onAddBatch, onUpdateBatch, onDeleteBatch }) => {
  const [isModalOpen, setIsModalOpen] = useState(false);
  const [editingId, setEditingId] = useState<string | null>(null);
  const blank = {
    name: '',
    classLevel: 'Class 9' as ClassLevel,
    scheduleDays: 'রবি · মঙ্গল · বৃহস্পতি',
    timeSlot: '',
    roomNumber: '',
    enrolledStudents: 0,
    maxCapacity: 35,
    instructor: 'Rafiqul Islam (Jewel Sir)',
    status: 'active' as 'active' | 'upcoming',
  };
  const [newBatch, setNewBatch] = useState(blank);

  const closeModal = () => {
    setIsModalOpen(false);
    setEditingId(null);
    setNewBatch(blank);
  };

  const openAdd = () => {
    setEditingId(null);
    setNewBatch(blank);
    setIsModalOpen(true);
  };

  const openEdit = (b: AdminBatchRecord) => {
    setEditingId(b.id);
    setNewBatch({
      name: b.name,
      classLevel: b.classLevel,
      scheduleDays: b.scheduleDays,
      timeSlot: b.timeSlot,
      roomNumber: b.roomNumber,
      enrolledStudents: b.enrolledStudents,
      maxCapacity: b.maxCapacity,
      instructor: b.instructor,
      status: b.status,
    });
    setIsModalOpen(true);
  };

  const handleSubmit = (e: React.FormEvent) => {
    e.preventDefault();
    if (!newBatch.name.trim()) {
      alert('ব্যাচের নাম প্রদান করুন।');
      return;
    }
    if (editingId) onUpdateBatch(editingId, newBatch);
    else onAddBatch(newBatch);
    closeModal();
  };

  const handleDelete = (b: AdminBatchRecord) => {
    const extra = b.enrolledStudents > 0
      ? `\nএই ব্যাচে ${b.enrolledStudents} জন শিক্ষার্থী আছে — তারা ব্যাচবিহীন হয়ে যাবে (শিক্ষার্থী মুছবে না)।`
      : '';
    if (confirm(`আপনি কি নিশ্চিত যে "${b.name}" (${b.scheduleDays} · ${b.timeSlot}) ব্যাচটি মুছে ফেলতে চান?${extra}`)) {
      onDeleteBatch(b.id);
    }
  };

  return (
    <div className="space-y-6">
      <SectionToolbar
        title="Programs & Batches"
        description="অষ্টম, নবম ও দশম শ্রেণির ব্যাচের সময়সূচি, আসন সংখ্যা ও শিক্ষক।"
      >
        <button onClick={openAdd} className="px-3.5 py-2 rounded-lg bg-blue-600 hover:bg-blue-700 text-white font-semibold text-xs flex items-center gap-1.5 transition-colors whitespace-nowrap">
          <Plus className="w-4 h-4" />
          <span>New Batch</span>
        </button>
      </SectionToolbar>

      {/* Batches Grid */}
      {batches.length === 0 ? (
        <div className="bg-white rounded-xl border border-slate-200">
          <EmptyState
            icon={BookOpen}
            title="No batches yet"
            description="ক্লাসের সময়সূচি অনুযায়ী প্রথম ব্যাচটি তৈরি করুন।"
            action={{ label: 'New Batch', onClick: openAdd, icon: Plus }}
          />
        </div>
      ) : (
      <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
        {batches.map((batch) => {
          const occupancyRate = batch.maxCapacity > 0 ? Math.min(100, Math.round((batch.enrolledStudents / batch.maxCapacity) * 100)) : 0;
          const isFull = batch.enrolledStudents >= batch.maxCapacity;

          return (
            <div
              key={batch.id}
              className="p-6 rounded-xl bg-white border border-slate-200 hover:border-blue-300 transition-all"
            >
              <div className="flex items-center justify-between mb-4">
                <span className="px-3 py-1 rounded-full text-xs font-bold text-blue-900 bg-sky-100 border border-sky-200">
                  {batch.classLevel}
                </span>
                <div className="flex items-center gap-1.5">
                  {isFull && (
                    <span className="text-[11px] font-bold text-rose-700 bg-rose-50 px-2 py-1 rounded-md border border-rose-200 font-bangla">Full</span>
                  )}
                  <span className="flex items-center gap-1.5 text-xs text-emerald-700 font-semibold bg-emerald-50 px-2.5 py-1 rounded-md border border-emerald-200">
                    <CheckCircle2 className="w-3.5 h-3.5" />
                    <span>{batch.status === 'active' ? 'Active' : 'Upcoming'}</span>
                  </span>
                  <button
                    onClick={() => openEdit(batch)}
                    className="p-1.5 rounded-lg text-slate-400 hover:text-blue-600 hover:bg-blue-50 transition-colors"
                    title="Edit Batch"
                  >
                    <Edit className="w-4 h-4" />
                  </button>
                  <button
                    onClick={() => handleDelete(batch)}
                    className="p-1.5 rounded-lg text-slate-400 hover:text-rose-600 hover:bg-rose-50 transition-colors"
                    title="Delete Batch"
                  >
                    <Trash2 className="w-4 h-4" />
                  </button>
                </div>
              </div>

              <h3 className="text-lg font-bold text-slate-900 font-bangla mb-3">
                {batch.name}
              </h3>

              <div className="space-y-2.5 text-xs text-slate-600 mb-5">
                <div className="flex items-center gap-2">
                  <Clock className="w-4 h-4 text-blue-600 shrink-0" />
                  <span>{batch.scheduleDays} · {batch.timeSlot}</span>
                </div>
                <div className="flex items-center gap-2">
                  <MapPin className="w-4 h-4 text-blue-600 shrink-0" />
                  <span>{batch.roomNumber ? `${batch.roomNumber} · ` : ''}১০, জিলা স্কুল রোড একাডেমি ক্যাম্পাস</span>
                </div>
                <div className="flex items-center gap-2">
                  <User className="w-4 h-4 text-blue-600 shrink-0" />
                  <span>Teacher: <strong className="text-slate-800">{batch.instructor}</strong></span>
                </div>
              </div>

              {/* Enrollment Progress Bar */}
              <div className="pt-4 border-t border-slate-100">
                <div className="flex justify-between text-xs mb-1.5">
                  <span className="text-slate-500">ভর্তিকৃত শিক্ষার্থী:</span>
                  <span className="font-mono font-bold text-slate-900 tabular-nums">
                    {batch.enrolledStudents} / {batch.maxCapacity} ({occupancyRate}%)
                  </span>
                </div>
                <div className="w-full h-2 rounded-full bg-slate-100 overflow-hidden">
                  <div
                    className="h-full bg-gradient-to-r from-blue-600 to-sky-500 rounded-full"
                    style={{ width: `${occupancyRate}%` }}
                  />
                </div>
              </div>
            </div>
          );
        })}
      </div>
      )}

      {/* Add Batch Modal */}
      {isModalOpen && (
        <div className="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/60 backdrop-blur-sm p-4">
          <div className="relative max-w-md w-full bg-white rounded-xl border border-slate-200 shadow-2xl p-6">
            <button
              onClick={closeModal}
              className="absolute top-5 right-5 p-1.5 rounded-lg text-slate-400 hover:text-slate-800 bg-slate-100"
            >
              <X className="w-5 h-5" />
            </button>

            <h3 className="text-lg font-bold text-slate-900 font-bangla mb-1">
              {editingId ? 'Edit Batch' : 'Add Batch'}
            </h3>
            <p className="text-xs text-slate-500 mb-5">
              সময়সূচি ও সর্বোচ্চ ধারণক্ষমতা নির্ধারণ করুন।
            </p>

            <form onSubmit={handleSubmit} className="space-y-4">
              <div>
                <label className="block text-xs font-semibold text-slate-700 mb-1">
                  ব্যাচের শিরোনাম *
                </label>
                <input
                  type="text"
                  placeholder="যেমন: নবম শ্রেণি · ছেলেদের ব্যাচ"
                  value={newBatch.name}
                  onChange={(e) => setNewBatch({ ...newBatch, name: e.target.value })}
                  className="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-lg text-xs text-slate-900"
                  required
                />
              </div>

              <div className="grid grid-cols-2 gap-3">
                <div>
                  <label className="block text-xs font-semibold text-slate-700 mb-1">
                    শ্রেণি
                  </label>
                  <select
                    value={newBatch.classLevel}
                    onChange={(e) =>
                      setNewBatch({
                        ...newBatch,
                        classLevel: e.target.value as ClassLevel,
                      })
                    }
                    className="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-lg text-xs text-slate-900"
                  >
                    <option value="Class 8">Class 8</option>
            <option value="Class 9">Class 9</option>
                    <option value="Class 10">Class 10</option>
                  </select>
                </div>

                <div>
                  <label className="block text-xs font-semibold text-slate-700 mb-1">
                    সর্বোচ্চ আসন
                  </label>
                  <input
                    type="number"
                    value={newBatch.maxCapacity}
                    onChange={(e) => setNewBatch({ ...newBatch, maxCapacity: Number(e.target.value) })}
                    className="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-lg text-xs font-mono text-slate-900"
                  />
                </div>
              </div>

              <div>
                <label className="block text-xs font-semibold text-slate-700 mb-1">
                  ক্লাসের দিনসমূহ
                </label>
                <input
                  type="text"
                  placeholder="যেমন: শনি · সোম · বুধ"
                  value={newBatch.scheduleDays}
                  onChange={(e) => setNewBatch({ ...newBatch, scheduleDays: e.target.value })}
                  className="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-lg text-xs text-slate-900"
                />
              </div>

              <div className="grid grid-cols-2 gap-3">
                <div>
                  <label className="block text-xs font-semibold text-slate-700 mb-1">
                    সময়সূচি
                  </label>
                  <input
                    type="text"
                    placeholder="৪:০০ PM · ৫:৩০ PM"
                    value={newBatch.timeSlot}
                    onChange={(e) => setNewBatch({ ...newBatch, timeSlot: e.target.value })}
                    className="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-lg text-xs text-slate-900"
                  />
                </div>

                <div>
                  <label className="block text-xs font-semibold text-slate-700 mb-1">
                    রুম নম্বর
                  </label>
                  <input
                    type="text"
                    placeholder="Room 101"
                    value={newBatch.roomNumber}
                    onChange={(e) => setNewBatch({ ...newBatch, roomNumber: e.target.value })}
                    className="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-lg text-xs text-slate-900"
                  />
                </div>
              </div>

              <div className="grid grid-cols-2 gap-3">
                <div>
                  <label className="block text-xs font-semibold text-slate-700 mb-1">
                    শিক্ষক
                  </label>
                  <input
                    type="text"
                    value={newBatch.instructor}
                    onChange={(e) => setNewBatch({ ...newBatch, instructor: e.target.value })}
                    className="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-lg text-xs text-slate-900"
                  />
                </div>
                <div>
                  <label className="block text-xs font-semibold text-slate-700 mb-1">
                    স্ট্যাটাস
                  </label>
                  <select
                    value={newBatch.status}
                    onChange={(e) => setNewBatch({ ...newBatch, status: e.target.value as 'active' | 'upcoming' })}
                    className="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-lg text-xs text-slate-900"
                  >
                    <option value="active">Active</option>
                    <option value="upcoming">Upcoming</option>
                  </select>
                </div>
              </div>

              <div className="pt-4 border-t border-slate-100 flex items-center justify-end gap-3">
                <button
                  type="button"
                  onClick={closeModal}
                  className="px-4 py-2 text-xs font-medium text-slate-600 hover:text-slate-900 bg-slate-100 rounded-lg"
                >
                  Cancel
                </button>
                <button
                  type="submit"
                  className="px-5 py-2 text-xs font-bold text-white bg-blue-600 hover:bg-blue-700 rounded-lg shadow-sm"
                >
                  {editingId ? 'Save Changes' : 'Add Batch'}
                </button>
              </div>
            </form>
          </div>
        </div>
      )}
    </div>
  );
};
