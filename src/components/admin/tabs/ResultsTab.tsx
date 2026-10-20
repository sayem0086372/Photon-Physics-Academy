import React, { useState } from 'react';
import { Award, Plus, Trash2, CheckCircle2, TrendingUp, X } from 'lucide-react';
import { AdminExamResult, AdminStudent } from '../../../types/admin';
import { EmptyState } from '../ui/EmptyState';
import { SectionToolbar } from '../ui/SectionToolbar';
import { StatusBadge } from '../ui/StatusBadge';

interface ResultsTabProps {
  results: AdminExamResult[];
  students: AdminStudent[];
  onAddResult: (res: Omit<AdminExamResult, 'id'>) => void;
  onDeleteResult: (id: string) => void;
}

export const ResultsTab: React.FC<ResultsTabProps> = ({
  results,
  students,
  onAddResult,
  onDeleteResult,
}) => {
  const [isModalOpen, setIsModalOpen] = useState(false);
  const [newResult, setNewResult] = useState({
    studentId: students[0]?.id || '',
    examTitle: 'Chapter 2: Kinematics Assessment',
    chapter: 'গতি',
    totalMarks: 50,
    obtainedMarks: 45,
    highestMarks: 49,
    grade: 'A+',
    examDate: new Date().toISOString().split('T')[0],
  });

  const handleSubmit = (e: React.FormEvent) => {
    e.preventDefault();
    const student = students.find((s) => s.id === newResult.studentId);
    if (!student) {
      alert('শিক্ষার্থী নির্বাচন করুন।');
      return;
    }

    onAddResult({
      studentIdCode: student.studentIdCode,
      studentName: student.fullName,
      classLevel: student.classLevel,
      examTitle: newResult.examTitle,
      chapter: newResult.chapter,
      totalMarks: newResult.totalMarks,
      obtainedMarks: newResult.obtainedMarks,
      highestMarks: newResult.highestMarks,
      grade: newResult.grade,
      examDate: newResult.examDate,
    });

    setIsModalOpen(false);
  };

  return (
    <div className="space-y-6">
      <SectionToolbar
        title="Results & Exams"
        description="অধ্যায়ভিত্তিক পরীক্ষার প্রাপ্ত নম্বর ও গ্রেডের রেকর্ড।"
      >
        <button onClick={() => setIsModalOpen(true)} className="px-3.5 py-2 rounded-lg bg-blue-600 hover:bg-blue-700 text-white font-semibold text-xs flex items-center gap-1.5 transition-colors whitespace-nowrap">
          <Plus className="w-4 h-4" />
          <span>Add Marks</span>
        </button>
      </SectionToolbar>

      {/* Results Table */}
      <div className="rounded-xl bg-white border border-slate-200 overflow-hidden">
        <div className="overflow-x-auto">
          <table className="w-full text-left text-xs">
            <thead className="bg-slate-50 border-b border-slate-200 text-slate-500 font-semibold">
              <tr>
                <th className="py-3.5 px-4">ID</th>
                <th className="py-3.5 px-4">Student</th>
                <th className="py-3.5 px-4">Exam & Chapter</th>
                <th className="py-3.5 px-4 text-center">Total</th>
                <th className="py-3.5 px-4 text-center">Obtained</th>
                <th className="py-3.5 px-4 text-center">Highest</th>
                <th className="py-3.5 px-4 text-center">Grade</th>
                <th className="py-3.5 px-4 text-right">Date</th>
                <th className="py-3.5 px-4 text-center">Actions</th>
              </tr>
            </thead>
            <tbody className="divide-y divide-slate-100">
              {results.length === 0 && (
                <tr>
                  <td colSpan={9}>
                    <EmptyState
                      icon={Award}
                      title="No exam results yet"
                      description="পরীক্ষার নম্বর এন্ট্রি করলে ফলাফলের তালিকা এখানে দেখা যাবে।"
                      action={{ label: 'Add Marks', onClick: () => setIsModalOpen(true), icon: Plus }}
                    />
                  </td>
                </tr>
              )}
              {results.map((r) => (
                <tr key={r.id} className="hover:bg-slate-50/80 transition-colors">
                  <td className="py-3 px-4 font-mono font-medium text-slate-600">{r.studentIdCode}</td>
                  <td className="py-3 px-4">
                    <div className="font-bold text-slate-900">{r.studentName}</div>
                    <div className="text-[10px] text-slate-400">{r.classLevel}</div>
                  </td>
                  <td className="py-3 px-4">
                    <div className="font-semibold text-slate-800">{r.examTitle}</div>
                    <div className="text-[11px] text-blue-700 font-bangla">{r.chapter}</div>
                  </td>
                  <td className="py-3 px-4 text-center font-mono text-slate-600 tabular-nums">
                    {r.totalMarks}
                  </td>
                  <td className="py-3 px-4 text-center font-mono font-bold text-blue-700 tabular-nums">
                    {r.obtainedMarks}
                  </td>
                  <td className="py-3 px-4 text-center font-mono text-slate-500 tabular-nums">
                    {r.highestMarks}
                  </td>
                  <td className="py-3 px-4 text-center">
                    <span className="px-2 py-0.5 rounded bg-emerald-50 text-emerald-700 font-bold border border-emerald-200 text-[11px]">
                      {r.grade}
                    </span>
                  </td>
                  <td className="py-3 px-4 text-right font-mono text-slate-500">
                    {r.examDate}
                  </td>
                  <td className="py-3 px-4 text-center">
                    <button
                      onClick={() => {
                        if (confirm('আপনি কি এই রেজাল্ট রেকর্ড মুছে ফেলতে চান?')) {
                          onDeleteResult(r.id);
                        }
                      }}
                      className="p-1.5 rounded-lg text-slate-400 hover:text-rose-600 hover:bg-rose-50 transition-colors"
                      title="Delete Record"
                    >
                      <Trash2 className="w-4 h-4" />
                    </button>
                  </td>
                </tr>
              ))}
            </tbody>
          </table>
        </div>
      </div>

      {/* Add Result Modal */}
      {isModalOpen && (
        <div className="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/60 backdrop-blur-sm p-4">
          <div className="relative max-w-md w-full bg-white rounded-xl border border-slate-200 shadow-2xl p-6">
            <button
              onClick={() => setIsModalOpen(false)}
              className="absolute top-5 right-5 p-1.5 rounded-lg text-slate-400 hover:text-slate-800 bg-slate-100"
            >
              <X className="w-5 h-5" />
            </button>

            <h3 className="text-lg font-bold text-slate-900 font-bangla mb-1">
              Record Marks
            </h3>
            <p className="text-xs text-slate-500 mb-5">
              শিক্ষার্থীর অধ্যায়ভিত্তিক মূল্যায়ন ডাটাবেসে যুক্ত করুন।
            </p>

            <form onSubmit={handleSubmit} className="space-y-4">
              <div>
                <label className="block text-xs font-semibold text-slate-700 mb-1">
                  শিক্ষার্থী নির্বাচন করুন *
                </label>
                <select
                  value={newResult.studentId}
                  onChange={(e) => setNewResult({ ...newResult, studentId: e.target.value })}
                  className="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-lg text-xs font-medium text-slate-900"
                  required
                >
                  {students.map((s) => (
                    <option key={s.id} value={s.id}>
                      {s.fullName} ({s.studentIdCode} - {s.classLevel})
                    </option>
                  ))}
                </select>
              </div>

              <div>
                <label className="block text-xs font-semibold text-slate-700 mb-1">
                  পরীক্ষার শিরোনাম *
                </label>
                <input
                  type="text"
                  value={newResult.examTitle}
                  onChange={(e) => setNewResult({ ...newResult, examTitle: e.target.value })}
                  className="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-lg text-xs text-slate-900"
                  required
                />
              </div>

              <div>
                <label className="block text-xs font-semibold text-slate-700 mb-1">
                  অধ্যায় / টপিক
                </label>
                <input
                  type="text"
                  value={newResult.chapter}
                  onChange={(e) => setNewResult({ ...newResult, chapter: e.target.value })}
                  className="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-lg text-xs font-bangla text-slate-900"
                  required
                />
              </div>

              <div className="grid grid-cols-3 gap-2">
                <div>
                  <label className="block text-xs font-semibold text-slate-700 mb-1">
                    মোট নম্বর
                  </label>
                  <input
                    type="number"
                    value={newResult.totalMarks}
                    onChange={(e) => setNewResult({ ...newResult, totalMarks: Number(e.target.value) })}
                    className="w-full px-2.5 py-2 bg-slate-50 border border-slate-200 rounded-lg text-xs font-mono text-slate-900"
                  />
                </div>

                <div>
                  <label className="block text-xs font-semibold text-slate-700 mb-1">
                    প্রাপ্ত নম্বর *
                  </label>
                  <input
                    type="number"
                    step="0.5"
                    value={newResult.obtainedMarks}
                    onChange={(e) => setNewResult({ ...newResult, obtainedMarks: Number(e.target.value) })}
                    className="w-full px-2.5 py-2 bg-slate-50 border border-slate-200 rounded-lg text-xs font-mono font-bold text-blue-700"
                    required
                  />
                </div>

                <div>
                  <label className="block text-xs font-semibold text-slate-700 mb-1">
                    গ্রেড
                  </label>
                  <select
                    value={newResult.grade}
                    onChange={(e) => setNewResult({ ...newResult, grade: e.target.value })}
                    className="w-full px-2.5 py-2 bg-slate-50 border border-slate-200 rounded-lg text-xs font-bold text-slate-900"
                  >
                    <option value="A+">A+</option>
                    <option value="A">A</option>
                    <option value="A-">A-</option>
                    <option value="B">B</option>
                    <option value="C">C</option>
                    <option value="F">F</option>
                  </select>
                </div>
              </div>

              <div className="pt-4 border-t border-slate-100 flex items-center justify-end gap-3">
                <button
                  type="button"
                  onClick={() => setIsModalOpen(false)}
                  className="px-4 py-2 text-xs font-medium text-slate-600 hover:text-slate-900 bg-slate-100 rounded-lg"
                >
                  Cancel
                </button>
                <button
                  type="submit"
                  className="px-5 py-2 text-xs font-bold text-white bg-blue-600 hover:bg-blue-700 rounded-lg shadow-sm"
                >
                  Save Marks
                </button>
              </div>
            </form>
          </div>
        </div>
      )}
    </div>
  );
};
