import React, { useState } from 'react';
import { Search, UserPlus, Phone, Trash2, Edit, X, CheckCircle, Building, GraduationCap } from 'lucide-react';
import { AdminStudent, AdminBatchRecord, ClassLevel } from '../../../types/admin';
import { EmptyState } from '../ui/EmptyState';
import { SectionToolbar } from '../ui/SectionToolbar';
import { StatusBadge } from '../ui/StatusBadge';

interface StudentsTabProps {
  students: AdminStudent[];
  batches: AdminBatchRecord[];
  onAddStudent: (student: Omit<AdminStudent, 'id' | 'joinedDate'>) => void;
  onUpdateStudent: (id: string, student: Omit<AdminStudent, 'id' | 'joinedDate'>) => void;
  onDeleteStudent: (id: string) => void;
  isAddModalOpen: boolean;
  setIsAddModalOpen: (open: boolean) => void;
}

export const StudentsTab: React.FC<StudentsTabProps> = ({
  students,
  batches,
  onAddStudent,
  onUpdateStudent,
  onDeleteStudent,
  isAddModalOpen,
  setIsAddModalOpen,
}) => {
  const [searchTerm, setSearchTerm] = useState('');
  const [classFilter, setClassFilter] = useState<string>('All');
  const [paymentFilter, setPaymentFilter] = useState<string>('All');

  // Form state for adding new student
  const [formData, setFormData] = useState({
    studentIdCode: `PPA-26-09-0${students.length + 1}`,
    fullName: '',
    banglaName: '',
    classLevel: 'Class 9' as ClassLevel,
    studentPhone: '',
    guardianName: '',
    guardianPhone: '',
    whatsappPhone: '',
    institution: '',
    batchTiming: '',
    batchId: '' as string | null,
    monthlyFeeBdt: 1500,
    paymentStatus: 'Due' as 'Paid' | 'Partial' | 'Due',
    status: 'active' as 'active' | 'inactive',
  });

  const [editingId, setEditingId] = useState<string | null>(null);

  const emptyForm = (cls: ClassLevel = 'Class 9', count = students.length + 1) => ({
    studentIdCode: `PPA-26-${cls === 'Class 8' ? '08' : cls === 'Class 9' ? '09' : '10'}-0${count}`,
    fullName: '',
    banglaName: '',
    classLevel: cls,
    studentPhone: '',
    guardianName: '',
    guardianPhone: '',
    whatsappPhone: '',
    institution: '',
    batchTiming: '',
    batchId: '' as string | null,
    monthlyFeeBdt: 1500,
    paymentStatus: 'Due' as 'Paid' | 'Partial' | 'Due',
    status: 'active' as 'active' | 'inactive',
  });

  const closeModal = () => {
    setIsAddModalOpen(false);
    setEditingId(null);
    setFormData(emptyForm('Class 9', students.length + 2));
  };

  const openEdit = (st: AdminStudent) => {
    setEditingId(st.id);
    setFormData({
      studentIdCode: st.studentIdCode,
      fullName: st.fullName,
      banglaName: st.banglaName ?? '',
      classLevel: st.classLevel,
      studentPhone: st.studentPhone,
      guardianName: st.guardianName,
      guardianPhone: st.guardianPhone,
      whatsappPhone: st.whatsappPhone ?? '',
      institution: st.institution,
      batchTiming: st.batchTiming,
      batchId: st.batchId ?? '',
      monthlyFeeBdt: st.monthlyFeeBdt,
      paymentStatus: st.paymentStatus,
      status: st.status,
    });
    setIsAddModalOpen(true);
  };

  const batchLabel = (b: AdminBatchRecord) => `${b.name} · ${b.scheduleDays} · ${b.timeSlot}`;
  const classBatches = batches.filter((b) => b.classLevel === formData.classLevel);
  const selectedBatch = batches.find((b) => b.id === formData.batchId);

  const handleFormSubmit = (e: React.FormEvent) => {
    e.preventDefault();
    if (!formData.fullName.trim()) {
      alert('অনুগ্রহ করে শিক্ষার্থীর নাম প্রদান করুন।');
      return;
    }
    const payload = {
      ...formData,
      batchId: formData.batchId || null,
      batchTiming: selectedBatch ? `${selectedBatch.scheduleDays} · ${selectedBatch.timeSlot}`.slice(0, 100) : formData.batchTiming,
    };
    if (editingId) onUpdateStudent(editingId, payload);
    else onAddStudent(payload);
    closeModal();
  };

  // Filter logic
  const filteredStudents = students.filter((s) => {
    const matchesSearch =
      s.fullName.toLowerCase().includes(searchTerm.toLowerCase()) ||
      s.studentIdCode.toLowerCase().includes(searchTerm.toLowerCase()) ||
      s.studentPhone.includes(searchTerm) ||
      s.institution.toLowerCase().includes(searchTerm.toLowerCase()) ||
      (s.banglaName && s.banglaName.includes(searchTerm));

    const matchesClass = classFilter === 'All' || s.classLevel === classFilter;
    const matchesPayment = paymentFilter === 'All' || s.paymentStatus === paymentFilter;

    return matchesSearch && matchesClass && matchesPayment;
  });

  return (
    <div className="space-y-6">
      {/* Controls Bar */}
      <div className="flex flex-col sm:flex-row items-stretch sm:items-center justify-between gap-4 bg-white p-4 rounded-xl border border-slate-200">
        {/* Search */}
        <div className="relative flex-1">
          <Search className="w-4 h-4 absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400" />
          <input
            type="text"
            placeholder="শিক্ষার্থী নাম, আইডি কোড, স্কুল বা ফোন নম্বর..."
            value={searchTerm}
            onChange={(e) => setSearchTerm(e.target.value)}
            className="w-full pl-9 pr-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs sm:text-sm text-slate-900 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-blue-500"
          />
        </div>

        {/* Filters and Add button */}
        <div className="flex flex-wrap items-center gap-2">
          {/* Class Filter */}
          <select
            value={classFilter}
            onChange={(e) => setClassFilter(e.target.value)}
            className="px-3 py-2.5 rounded-xl border border-slate-200 bg-slate-50 text-xs font-medium text-slate-700 focus:outline-none"
          >
            <option value="All">All Classes</option>
            <option value="Class 8">Class 8</option>
            <option value="Class 9">Class 9</option>
            <option value="Class 10">Class 10</option>
          </select>

          {/* Payment Status Filter */}
          <select
            value={paymentFilter}
            onChange={(e) => setPaymentFilter(e.target.value)}
            className="px-3 py-2.5 rounded-xl border border-slate-200 bg-slate-50 text-xs font-medium text-slate-700 focus:outline-none"
          >
            <option value="All">All Fee Status</option>
            <option value="Paid">Paid</option>
            <option value="Partial">Partial</option>
            <option value="Due">Due</option>
          </select>

          {/* Add Student Button */}
          <button
            onClick={() => setIsAddModalOpen(true)}
            className="px-4 py-2.5 rounded-xl bg-blue-600 hover:bg-blue-700 text-white font-semibold text-xs flex items-center gap-1.5 shadow-sm transition-colors whitespace-nowrap"
          >
            <UserPlus className="w-4 h-4" />
            <span>Add Student</span>
          </button>
        </div>
      </div>

      {/* Students Data Grid / Table */}
      <div className="rounded-xl bg-white border border-slate-200 overflow-hidden">
        <div className="overflow-x-auto">
          <table className="w-full text-left text-xs">
            <thead className="bg-slate-50 border-b border-slate-200 text-slate-500 font-semibold">
              <tr>
                <th className="py-3.5 px-4">ID</th>
                <th className="py-3.5 px-4">Student</th>
                <th className="py-3.5 px-4">Class & Batch</th>
                <th className="py-3.5 px-4">Contact</th>
                <th className="py-3.5 px-4">Institution</th>
                <th className="py-3.5 px-4 text-right">Monthly Fee</th>
                <th className="py-3.5 px-4">Payment</th>
                <th className="py-3.5 px-4 text-center">Actions</th>
              </tr>
            </thead>
            <tbody className="divide-y divide-slate-100">
              {filteredStudents.length > 0 ? (
                filteredStudents.map((student) => (
                  <tr key={student.id} className="hover:bg-slate-50/80 transition-colors">
                    <td className="py-3 px-4 font-mono font-medium text-slate-600">
                      {student.studentIdCode}
                    </td>
                    <td className="py-3 px-4">
                      <div className="font-bold text-slate-900">{student.fullName}</div>
                      {student.banglaName && (
                        <div className="text-[11px] text-slate-400 font-bangla">{student.banglaName}</div>
                      )}
                    </td>
                    <td className="py-3 px-4">
                      <div className="font-semibold text-slate-800">{student.classLevel}</div>
                      <div className="text-[10px] text-slate-500 font-bangla">
                        {batches.find((b) => b.id === student.batchId)?.name ?? student.batchTiming ?? ''}
                      </div>
                      {student.batchId && (
                        <div className="text-[10px] text-slate-400 font-bangla">{batches.find((b) => b.id === student.batchId)?.timeSlot}</div>
                      )}
                      {!student.batchId && (
                        <div className="text-[10px] text-amber-600 font-bangla">ব্যাচ নির্ধারিত নয়</div>
                      )}
                    </td>
                    <td className="py-3 px-4 space-y-0.5">
                      <div className="flex items-center gap-1 font-mono text-slate-700">
                        <Phone className="w-3 h-3 text-blue-600" />
                        <span>{student.studentPhone || 'No student phone'}</span>
                      </div>
                      <div className="text-[10px] text-slate-400 font-bangla">
                        অভিভাবক: {student.guardianName || student.guardianPhone || 'Not provided'}
                      </div>
                    </td>
                    <td className="py-3 px-4 text-slate-600">
                      <div className="flex items-center gap-1.5">
                        <Building className="w-3.5 h-3.5 text-slate-400 shrink-0" />
                        <span className="truncate max-w-[150px]">{student.institution}</span>
                      </div>
                    </td>
                    <td className="py-3 px-4 text-right font-mono font-bold text-slate-900 tabular-nums">
                      ৳{student.monthlyFeeBdt.toLocaleString()}
                    </td>
                    <td className="py-3 px-4">
                      <StatusBadge status={student.paymentStatus} />
                    </td>
                    <td className="py-3 px-4 text-center whitespace-nowrap">
                      <button
                        onClick={() => openEdit(student)}
                        className="p-1.5 rounded-lg text-slate-400 hover:text-blue-600 hover:bg-blue-50 transition-colors"
                        title="Edit Student"
                      >
                        <Edit className="w-4 h-4" />
                      </button>
                      <button
                        onClick={() => {
                          if (confirm(`আপনি কি ${student.fullName} এর রেকর্ড মুছে ফেলতে চান?`)) {
                            onDeleteStudent(student.id);
                          }
                        }}
                        className="p-1.5 rounded-lg text-slate-400 hover:text-rose-600 hover:bg-rose-50 transition-colors"
                        title="Delete Student"
                      >
                        <Trash2 className="w-4 h-4" />
                      </button>
                    </td>
                  </tr>
                ))
              ) : (
                <tr>
                  <td colSpan={8}>
                    {students.length === 0 ? (
                      <EmptyState
                        icon={GraduationCap}
                        title="No students yet"
                        description="এখনো কোনো শিক্ষার্থী যুক্ত করা হয়নি। প্রথম শিক্ষার্থী ভর্তি করে শুরু করুন।"
                        action={{ label: 'Add Student', onClick: () => setIsAddModalOpen(true), icon: UserPlus }}
                      />
                    ) : (
                      <EmptyState
                        icon={Search}
                        title="No matching students"
                        description="অনুসন্ধান বা ফিল্টার পরিবর্তন করে আবার চেষ্টা করুন।"
                      />
                    )}
                  </td>
                </tr>
              )}
            </tbody>
          </table>
        </div>

        {/* Count summary */}
        <div className="p-4 bg-slate-50 border-t border-slate-100 flex items-center justify-between text-xs text-slate-500">
          <span>
            Showing <strong>{filteredStudents.length}</strong> of <strong>{students.length}</strong> students
          </span>
        </div>
      </div>

      {/* Add Student Modal */}
      {isAddModalOpen && (
        <div className="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/60 backdrop-blur-sm p-4">
          <div className="relative max-w-lg w-full bg-white rounded-xl border border-slate-200 shadow-2xl p-6 max-h-[90vh] overflow-y-auto">
            <button
              onClick={closeModal}
              className="absolute top-5 right-5 p-1.5 rounded-lg text-slate-400 hover:text-slate-800 bg-slate-100"
            >
              <X className="w-5 h-5" />
            </button>

            <h3 className="text-lg font-bold text-slate-900 font-bangla mb-1">
              {editingId ? 'Edit Student' : 'Add Student'}
            </h3>
            <p className="text-xs text-slate-500 mb-5">
              একাডেমিক রেকর্ড ও মাসিক ফি ট্র্যাকিংয়ের জন্য তথ্য পূরণ করুন।
            </p>

            <form onSubmit={handleFormSubmit} className="space-y-4">
              <div className="grid grid-cols-2 gap-3">
                <div>
                  <label className="block text-xs font-semibold text-slate-700 mb-1">
                    শিক্ষার্থী আইডি কোড
                  </label>
                  <input
                    type="text"
                    value={formData.studentIdCode}
                    onChange={(e) => setFormData({ ...formData, studentIdCode: e.target.value })}
                    className="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-lg text-xs font-mono font-bold text-slate-800"
                    required
                  />
                </div>

                <div>
                  <label className="block text-xs font-semibold text-slate-700 mb-1">
                    শ্রেণি
                  </label>
                  <select
                    value={formData.classLevel}
                    onChange={(e) =>
                      setFormData({
                        ...formData,
                        classLevel: e.target.value as ClassLevel,
                        batchId: '',
                      })
                    }
                    className="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-lg text-xs font-medium text-slate-800"
                  >
                    <option value="Class 8">Class 8</option>
            <option value="Class 9">Class 9</option>
                    <option value="Class 10">Class 10</option>
                  </select>
                </div>
              </div>

              <div>
                <label className="block text-xs font-semibold text-slate-700 mb-1">
                  ব্যাচ
                </label>
                <select
                  value={formData.batchId ?? ''}
                  onChange={(e) => setFormData({ ...formData, batchId: e.target.value })}
                  className="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-lg text-xs text-slate-800 font-bangla"
                >
                  <option value="">— ব্যাচ নির্বাচন করুন (ঐচ্ছিক) —</option>
                  {classBatches.map((b) => {
                    const full = b.enrolledStudents >= b.maxCapacity && b.id !== formData.batchId;
                    return (
                      <option key={b.id} value={b.id} disabled={full}>
                        {batchLabel(b)} ({b.enrolledStudents}/{b.maxCapacity}{full ? ' — পূর্ণ' : ''})
                      </option>
                    );
                  })}
                </select>
                {classBatches.length === 0 && (
                  <p className="text-[11px] text-amber-600 mt-1 font-bangla">এই শ্রেণির জন্য কোনো ব্যাচ নেই। আগে "ব্যাচ" ট্যাবে ব্যাচ তৈরি করুন।</p>
                )}
              </div>

              <div>
                <label className="block text-xs font-semibold text-slate-700 mb-1">
                  শিক্ষার্থীর নাম (ইংরেজিতে) *
                </label>
                <input
                  type="text"
                  placeholder="e.g. Tanvir Ahmed"
                  value={formData.fullName}
                  onChange={(e) => setFormData({ ...formData, fullName: e.target.value })}
                  className="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-lg text-xs text-slate-900"
                  required
                />
              </div>

              <div>
                <label className="block text-xs font-semibold text-slate-700 mb-1">
                  বাংলা নাম (ঐচ্ছিক)
                </label>
                <input
                  type="text"
                  placeholder="যেমন: তানভীর আহমেদ"
                  value={formData.banglaName}
                  onChange={(e) => setFormData({ ...formData, banglaName: e.target.value })}
                  className="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-lg text-xs font-bangla text-slate-900"
                />
              </div>

              <div className="grid grid-cols-2 gap-3">
                <div>
                  <label className="block text-xs font-semibold text-slate-700 mb-1">
                    শিক্ষার্থীর ফোন নম্বর (ঐচ্ছিক)
                  </label>
                  <input
                    type="text"
                    placeholder="01712-345678"
                    value={formData.studentPhone}
                    onChange={(e) => setFormData({ ...formData, studentPhone: e.target.value })}
                    className="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-lg text-xs font-mono text-slate-900"
                  />
                </div>

                <div>
                  <label className="block text-xs font-semibold text-slate-700 mb-1">
                    স্কুল / শিক্ষাপ্রতিষ্ঠান
                  </label>
                  <input
                    type="text"
                    placeholder="e.g. Mymensingh Zilla School"
                    value={formData.institution}
                    onChange={(e) => setFormData({ ...formData, institution: e.target.value })}
                    className="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-lg text-xs text-slate-900"
                  />
                </div>
              </div>

              <div className="grid grid-cols-2 gap-3">
                <div>
                  <label className="block text-xs font-semibold text-slate-700 mb-1">
                    অভিভাবকের নাম
                  </label>
                  <input
                    type="text"
                    placeholder="Guardian Name"
                    value={formData.guardianName}
                    onChange={(e) => setFormData({ ...formData, guardianName: e.target.value })}
                    className="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-lg text-xs text-slate-900"
                  />
                </div>

                <div>
                  <label className="block text-xs font-semibold text-slate-700 mb-1">
                    অভিভাবকের ফোন
                  </label>
                  <input
                    type="text"
                    placeholder="01711-223344"
                    value={formData.guardianPhone}
                    onChange={(e) => setFormData({ ...formData, guardianPhone: e.target.value })}
                    className="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-lg text-xs font-mono text-slate-900"
                  />
                </div>
              </div>

              <div>
                <label className="block text-xs font-semibold text-slate-700 mb-1">WhatsApp Number (Optional)</label>
                <input
                  type="tel"
                  placeholder="e.g. 8801712345678"
                  value={formData.whatsappPhone}
                  onChange={(e) => setFormData({ ...formData, whatsappPhone: e.target.value })}
                  className="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-lg text-xs font-mono text-slate-900"
                />
                <p className="mt-1 text-[10px] text-slate-500">Saved as contact information only. SMS gateway does not send WhatsApp messages.</p>
              </div>

              <div className="grid grid-cols-2 gap-3">
                <div>
                  <label className="block text-xs font-semibold text-slate-700 mb-1">
                    মাসিক ফি (৳)
                  </label>
                  <input
                    type="number"
                    value={formData.monthlyFeeBdt}
                    onChange={(e) => setFormData({ ...formData, monthlyFeeBdt: Number(e.target.value) })}
                    className="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-lg text-xs font-mono text-slate-900"
                  />
                </div>

                <div>
                  <label className="block text-xs font-semibold text-slate-700 mb-1">
                    প্রাথমিক পেমেন্ট স্ট্যাটাস
                  </label>
                  <select
                    value={formData.paymentStatus}
                    onChange={(e) =>
                      setFormData({
                        ...formData,
                        paymentStatus: e.target.value as 'Paid' | 'Partial' | 'Due',
                      })
                    }
                    className="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-lg text-xs text-slate-900"
                  >
                    <option value="Due">Due</option>
                    <option value="Paid">Paid</option>
                    <option value="Partial">Partial</option>
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
                  {editingId ? 'Save Changes' : 'Save Student'}
                </button>
              </div>
            </form>
          </div>
        </div>
      )}
    </div>
  );
};
