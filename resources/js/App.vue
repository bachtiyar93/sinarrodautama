<script setup>
import { ref, onMounted, computed } from 'vue';
import axios from 'axios';

// State
const tasks = ref([]);
const users = ref([]);
const isLoading = ref(false);
const filter = ref('all');
const activeMenu = ref('Tasklist');
const selectedTasks = ref([]); // Untuk delete massal

// Modals
const showTaskModal = ref(false);
const showUserModal = ref(false);
const editingTask = ref(null);
const editingUser = ref(null);

// Form States
const taskForm = ref({ title: '', description: '', start_date: '', end_date: '', assigned_to: '' });
const userForm = ref({ name: '', email: '', password: '' });
const toast = ref({ show: false, message: '', type: 'success' });
const errors = ref({});

// API Actions
const fetchData = async () => {
    isLoading.value = true;
    try {
        const [tasksRes, usersRes] = await Promise.all([
            axios.get('/api/v1/tasks'),
            axios.get('/api/v1/users')
        ]);
        tasks.value = tasksRes.data.data;
        users.value = usersRes.data.data;
    } catch (error) {
        showToast('Gagal memuat data', 'error');
    } finally {
        isLoading.value = false;
    }
};

// Task Actions
const saveTask = async () => {
    errors.value = {};

    // Frontend Validation
    if (!taskForm.value.start_date || !taskForm.value.end_date) {
        if (!taskForm.value.start_date) errors.value.start_date = ['Tgl mulai wajib diisi'];
        if (!taskForm.value.end_date) errors.value.end_date = ['Tgl selesai wajib diisi'];
        return;
    }

    if (new Date(taskForm.value.start_date) > new Date(taskForm.value.end_date)) {
        errors.value.end_date = ['Tgl selesai tidak boleh mendahului tgl mulai'];
        return;
    }

    if (!taskForm.value.assigned_to) {
        errors.value.assigned_to = ['Karyawan harus dipilih'];
        return;
    }

    try {
        if (editingTask.value) {
            const res = await axios.put(`/api/v1/tasks/${editingTask.value.id}`, taskForm.value);
            const index = tasks.value.findIndex(t => t.id === editingTask.value.id);
            tasks.value[index] = res.data.data;
            showToast('Tugas diperbarui');
        } else {
            const res = await axios.post('/api/v1/tasks', taskForm.value);
            tasks.value.unshift(res.data.data);
            showToast('Tugas baru ditambahkan');
        }
        closeTaskModal();
    } catch (error) {
        if (error.response?.status === 422) {
            errors.value = error.response.data.errors;
        } else {
            showToast('Gagal menyimpan tugas', 'error');
        }
    }
};

const toggleTaskStatus = async (task) => {
    try {
        const res = await axios.put(`/api/v1/tasks/${task.id}`, { is_completed: !task.is_completed });
        const index = tasks.value.findIndex(t => t.id === task.id);
        tasks.value[index] = res.data.data;
        showToast('Status diperbarui');
    } catch (error) {
        showToast('Gagal update status', 'error');
    }
};

const deleteSelectedTasks = async () => {
    if (selectedTasks.value.length === 0) return;
    if (!confirm(`Hapus ${selectedTasks.value.length} tugas terpilih?`)) return;

    try {
        await Promise.all(selectedTasks.value.map(id => axios.delete(`/api/v1/tasks/${id}`)));
        tasks.value = tasks.value.filter(t => !selectedTasks.value.includes(t.id));
        selectedTasks.value = [];
        showToast('Tugas terpilih berhasil dihapus');
    } catch (error) {
        showToast('Gagal menghapus beberapa tugas', 'error');
    }
};

const deleteTask = async (id) => {
    if (!confirm('Hapus tugas ini?')) return;
    try {
        await axios.delete(`/api/v1/tasks/${id}`);
        tasks.value = tasks.value.filter(t => t.id !== id);
        showToast('Tugas dihapus');
    } catch (error) {
        showToast('Gagal menghapus', 'error');
    }
};

// User Actions
const saveUser = async () => {
    errors.value = {};
    try {
        if (editingUser.value) {
            const res = await axios.put(`/api/v1/users/${editingUser.value.id}`, userForm.value);
            const index = users.value.findIndex(u => u.id === editingUser.value.id);
            users.value[index] = res.data.data;
            showToast('Data karyawan diperbarui');
        } else {
            const res = await axios.post('/api/v1/users', userForm.value);
            users.value.unshift(res.data.data);
            showToast('Karyawan baru ditambahkan');
        }
        closeUserModal();
        fetchData();
    } catch (error) {
        if (error.response?.status === 422) {
            errors.value = error.response.data.errors;
        } else {
            showToast('Gagal menyimpan karyawan', 'error');
        }
    }
};

const deleteUser = async (id) => {
    if (!confirm('Hapus karyawan ini?')) return;
    try {
        await axios.delete(`/api/v1/users/${id}`);
        users.value = users.value.filter(u => u.id !== id);
        showToast('Karyawan dihapus');
    } catch (error) {
        showToast('Gagal menghapus', 'error');
    }
};

// UI Helpers
const getTaskStatus = (task) => {
    if (task.is_completed) return { label: 'Done', color: 'bg-emerald-100 text-emerald-700 border-emerald-200' };

    const now = new Date().setHours(0,0,0,0);
    const start = new Date(task.start_date).setHours(0,0,0,0);
    const end = new Date(task.end_date).setHours(0,0,0,0);

    if (now < start) return { label: 'Pending', color: 'bg-amber-100 text-amber-700 border-amber-200' };
    if (now >= start && now <= end) return { label: 'Ongoing', color: 'bg-blue-100 text-blue-700 border-blue-200' };
    return { label: 'Overdue', color: 'bg-rose-100 text-rose-700 border-rose-200' };
};

const openTaskModal = (task = null) => {
    errors.value = {};
    if (task) {
        editingTask.value = task;
        taskForm.value = { ...task, assigned_to: task.assigned_to || '' };
    } else {
        editingTask.value = null;
        taskForm.value = { title: '', description: '', start_date: '', end_date: '', assigned_to: '' };
    }
    showTaskModal.value = true;
};

const closeTaskModal = () => { showTaskModal.value = false; editingTask.value = null; };

const openUserModal = (user = null) => {
    errors.value = {};
    if (user) {
        editingUser.value = user;
        userForm.value = { name: user.name, email: user.email, password: '' };
    } else {
        editingUser.value = null;
        userForm.value = { name: '', email: '', password: '' };
    }
    showUserModal.value = true;
};

const closeUserModal = () => { showUserModal.value = false; editingUser.value = null; };

const showToast = (message, type = 'success') => {
    toast.value = { show: true, message, type };
    setTimeout(() => toast.value.show = false, 3000);
};

const toggleSelectAll = () => {
    if (selectedTasks.value.length === filteredTasks.value.length) {
        selectedTasks.value = [];
    } else {
        selectedTasks.value = filteredTasks.value.map(t => t.id);
    }
};

const filteredTasks = computed(() => {
    if (filter.value === 'active') return tasks.value.filter(t => !t.is_completed);
    if (filter.value === 'completed') return tasks.value.filter(t => t.is_completed);
    return tasks.value;
});

onMounted(fetchData);
</script>

<template>
    <div class="min-h-screen bg-slate-50 font-sans text-slate-900 flex flex-col">
        <!-- Header -->
        <header class="bg-white border-b border-slate-200 sticky top-0 z-30 shadow-sm">
            <div class="max-w-7xl mx-auto px-4 h-16 flex items-center justify-between">
                <div class="flex items-center space-x-3">
                    <div class="w-10 h-10 bg-blue-600 rounded-lg flex items-center justify-center text-white font-bold shadow-lg shadow-blue-100">SR</div>
                    <div>
                        <h1 class="text-lg font-bold text-slate-800">PT. Sinar Roda Utama</h1>
                        <p class="text-[10px] text-slate-500 font-bold uppercase tracking-wider">Internal Management</p>
                    </div>
                </div>
                <div class="flex items-center space-x-2">
                    <div class="text-right hidden sm:block">
                        <p class="text-xs font-bold text-slate-800">Administrator</p>
                        <p class="text-[10px] text-slate-500">Super Admin</p>
                    </div>
                    <img src="https://ui-avatars.com/api/?name=Admin&background=0D8ABC&color=fff" class="w-8 h-8 rounded-full border border-slate-200">
                </div>
            </div>
        </header>

        <main class="flex-1 max-w-7xl mx-auto w-full px-4 py-8 grid grid-cols-1 lg:grid-cols-12 gap-8">
            <!-- Sidebar Left -->
            <aside class="lg:col-span-3 space-y-6">
                <nav class="bg-white border border-slate-200 rounded-[24px] p-3 shadow-sm">
                    <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest px-4 py-2">Menu Utama</p>
                    <div class="space-y-1">
                        <button v-for="menu in ['Tasklist', 'Employees']" :key="menu"
                            @click="activeMenu = menu"
                            :class="['w-full flex items-center px-4 py-3 rounded-xl text-sm font-bold transition-all',
                                     activeMenu === menu ? 'bg-blue-600 text-white shadow-lg shadow-blue-100' : 'text-slate-500 hover:bg-slate-50']">
                            <span class="mr-3">{{ menu === 'Tasklist' ? '📋' : '👥' }}</span>
                            {{ menu }}
                        </button>
                    </div>
                </nav>

                <div v-if="users.length === 0" class="bg-amber-50 border border-amber-200 rounded-[24px] p-5">
                    <p class="text-amber-800 text-xs font-bold mb-2">💡 Info Karyawan</p>
                    <p class="text-amber-700 text-[11px] leading-relaxed">Anda belum memiliki data karyawan. Buka menu <b>Employees</b> untuk menambahkan karyawan sebelum membuat tugas.</p>
                </div>

                <div class="bg-slate-900 rounded-[24px] p-6 text-white relative overflow-hidden">
                    <div class="relative z-10 text-center">
                        <p class="text-3xl font-black mb-1">{{ tasks.filter(t=>t.is_completed).length }}</p>
                        <p class="text-[10px] text-slate-400 font-bold uppercase tracking-widest">Tasks Done</p>
                    </div>
                </div>
            </aside>

            <!-- Content Area -->
            <div class="lg:col-span-9 space-y-6">

                <!-- TASKLIST VIEW -->
                <div v-if="activeMenu === 'Tasklist'" class="space-y-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <h2 class="text-2xl font-black text-slate-900">Task Management</h2>
                            <p class="text-sm text-slate-500">Gunakan checklist untuk menghapus massal</p>
                        </div>
                        <div class="flex gap-3">
                            <button v-if="selectedTasks.length > 0" @click="deleteSelectedTasks"
                                class="px-6 py-3 bg-rose-50 text-rose-600 font-bold rounded-2xl border border-rose-100 hover:bg-rose-100 transition-all text-sm">
                                🗑️ Hapus ({{ selectedTasks.length }})
                            </button>
                            <button @click="openTaskModal()" class="px-6 py-3 bg-blue-600 text-white font-black rounded-2xl shadow-xl shadow-blue-100 hover:-translate-y-1 transition-all text-sm">+ Tambah Task</button>
                        </div>
                    </div>

                    <div class="flex items-center justify-between pb-2 border-b border-slate-200">
                        <div class="flex gap-2">
                            <button v-for="t in ['all', 'active', 'completed']" :key="t" @click="filter = t"
                                :class="['px-5 py-2 rounded-full text-xs font-bold capitalize transition-all', filter === t ? 'bg-slate-900 text-white' : 'bg-white text-slate-500 hover:bg-slate-100']">
                                {{ t }}
                            </button>
                        </div>
                        <button @click="toggleSelectAll" class="text-[11px] font-bold text-blue-600 hover:underline">
                            {{ selectedTasks.length === filteredTasks.length ? 'Batalkan Semua' : 'Pilih Semua' }}
                        </button>
                    </div>

                    <div class="grid grid-cols-1 gap-4">
                        <div v-for="task in filteredTasks" :key="task.id" class="bg-white border border-slate-200 rounded-2xl p-5 hover:shadow-xl transition-all group relative">
                            <div class="flex items-start gap-4">
                                <!-- Multi-delete Checklist -->
                                <input type="checkbox" :value="task.id" v-model="selectedTasks"
                                    class="w-5 h-5 rounded border-slate-300 text-blue-600 focus:ring-blue-500 mt-1 cursor-pointer">

                                <div class="flex-1 min-w-0">
                                    <div class="flex items-center gap-3 mb-1">
                                        <h3 :class="['font-bold text-lg truncate', task.is_completed ? 'text-slate-400 line-through' : 'text-slate-800']">{{ task.title }}</h3>
                                        <span :class="['px-3 py-1 rounded-full text-[10px] font-black uppercase border', getTaskStatus(task).color]">
                                            {{ getTaskStatus(task).label }}
                                        </span>
                                    </div>
                                    <p class="text-sm text-slate-500 mb-4 line-clamp-2">{{ task.description || 'No description' }}</p>

                                    <div class="flex flex-wrap items-center gap-4 text-[11px] font-bold">
                                        <span class="flex items-center text-slate-400 bg-slate-50 px-2 py-1 rounded-md">📅 {{ task.start_date }} s/d {{ task.end_date }}</span>
                                        <span v-if="task.assignee" class="flex items-center text-blue-600 bg-blue-50 px-2 py-1 rounded-md">👤 {{ task.assignee.name }}</span>
                                    </div>
                                </div>

                                <!-- Toggle Selesai/Belum -->
                                <div class="flex items-center gap-4">
                                    <button @click="toggleTaskStatus(task)"
                                        :class="['relative inline-flex h-6 w-11 flex-shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out focus:outline-none',
                                            task.is_completed ? 'bg-emerald-500' : 'bg-slate-200']">
                                        <span :class="['inline-block h-5 w-5 transform rounded-full bg-white shadow ring-0 transition duration-200 ease-in-out',
                                            task.is_completed ? 'translate-x-5' : 'translate-x-0']"></span>
                                    </button>

                                    <div class="flex flex-col gap-1 opacity-0 group-hover:opacity-100 transition-opacity">
                                        <button @click="openTaskModal(task)" class="p-1.5 text-slate-400 hover:text-blue-600 hover:bg-blue-50 rounded-lg">✏️</button>
                                        <button @click="deleteTask(task.id)" class="p-1.5 text-slate-400 hover:text-red-600 hover:bg-red-50 rounded-lg">🗑️</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div v-if="filteredTasks.length === 0" class="text-center py-20 bg-slate-100/30 rounded-3xl border-2 border-dashed border-slate-200 text-slate-400 font-bold">Tidak ada tugas ditemukan.</div>
                    </div>
                </div>

                <!-- EMPLOYEES VIEW -->
                <div v-if="activeMenu === 'Employees'" class="space-y-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <h2 class="text-2xl font-black text-slate-900">Database Karyawan</h2>
                            <p class="text-sm text-slate-500">Kelola tim PT. Sinar Roda Utama di sini</p>
                        </div>
                        <button @click="openUserModal()" class="px-6 py-3 bg-blue-600 text-white font-black rounded-2xl shadow-xl shadow-blue-100 hover:-translate-y-1 transition-all text-sm">+ Tambah Karyawan</button>
                    </div>

                    <div class="bg-white border border-slate-200 rounded-[24px] overflow-hidden shadow-sm">
                        <table class="w-full text-left text-sm">
                            <thead class="bg-slate-50 border-b border-slate-200">
                                <tr>
                                    <th class="px-6 py-4 font-black text-slate-400 uppercase tracking-widest text-[10px]">Nama Karyawan</th>
                                    <th class="px-6 py-4 font-black text-slate-400 uppercase tracking-widest text-[10px]">Email</th>
                                    <th class="px-6 py-4"></th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                <tr v-for="user in users" :key="user.id" class="hover:bg-slate-50 transition-colors group">
                                    <td class="px-6 py-4 font-bold text-slate-800">{{ user.name }}</td>
                                    <td class="px-6 py-4 font-medium text-slate-500">{{ user.email }}</td>
                                    <td class="px-6 py-4 text-right">
                                        <div class="flex justify-end gap-2 opacity-0 group-hover:opacity-100 transition-opacity">
                                            <button @click="openUserModal(user)" class="p-2 hover:bg-blue-50 text-blue-600 rounded-lg">✏️</button>
                                            <button @click="deleteUser(user.id)" class="p-2 hover:bg-red-50 text-red-600 rounded-lg">🗑️</button>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

            </div>
        </main>

        <!-- Footer -->
        <footer class="bg-white border-t border-slate-200 py-6 mt-auto">
            <div class="max-w-7xl mx-auto px-4 text-center">
                <p class="text-slate-400 text-xs font-bold uppercase tracking-widest">
                    &copy; 2026 PT. Sinar Roda Utama - Internal System
                </p>
                <p class="text-slate-500 text-[10px] mt-1 font-medium">
                    Created by <span class="text-blue-600 font-bold">bachtiyar93</span>
                </p>
            </div>
        </footer>

        <!-- TASK MODAL -->
        <transition name="modal">
            <div v-if="showTaskModal" class="fixed inset-0 z-50 flex items-center justify-center p-4">
                <div class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm" @click="closeTaskModal"></div>
                <div class="bg-white rounded-[32px] w-full max-w-lg relative z-10 shadow-2xl p-8 max-h-[90vh] overflow-y-auto">
                    <h3 class="text-2xl font-black mb-6">{{ editingTask ? 'Edit Task' : 'Tambah Task' }}</h3>
                    <div class="space-y-4">
                        <div class="space-y-1">
                            <label class="text-[10px] font-black text-slate-400 uppercase ml-1">Judul Tugas</label>
                            <input v-model="taskForm.title" type="text" placeholder="Audit Inventaris..." class="w-full px-5 py-3 bg-slate-50 rounded-xl outline-none font-bold border-2 border-transparent focus:border-blue-500">
                            <p v-if="errors.title" class="text-rose-500 text-[10px] font-bold mt-1">{{ errors.title[0] }}</p>
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <div class="space-y-1">
                                <label class="text-[10px] font-black text-slate-400 uppercase ml-1">Tgl Mulai *</label>
                                <input v-model="taskForm.start_date" type="date" class="w-full px-5 py-3 bg-slate-50 rounded-xl outline-none font-bold border-2 border-transparent focus:border-blue-500">
                                <p v-if="errors.start_date" class="text-rose-500 text-[10px] font-bold mt-1">{{ errors.start_date[0] }}</p>
                            </div>
                            <div class="space-y-1">
                                <label class="text-[10px] font-black text-slate-400 uppercase ml-1">Tgl Selesai *</label>
                                <input v-model="taskForm.end_date" type="date" class="w-full px-5 py-3 bg-slate-50 rounded-xl outline-none font-bold border-2 border-transparent focus:border-blue-500">
                                <p v-if="errors.end_date" class="text-rose-500 text-[10px] font-bold mt-1">{{ errors.end_date[0] }}</p>
                            </div>
                        </div>

                        <div class="space-y-1">
                            <label class="text-[10px] font-black text-slate-400 uppercase ml-1">Karyawan Penanggung Jawab *</label>
                            <div v-if="users.length === 0" class="bg-amber-50 p-3 rounded-xl border border-amber-100 flex items-center gap-2">
                                <span class="text-xs text-amber-700 font-bold">Belum ada karyawan. <button @click="closeTaskModal(); activeMenu='Employees'" class="text-blue-600 underline">Tambah di menu Employees</button></span>
                            </div>
                            <select v-else v-model="taskForm.assigned_to" class="w-full px-5 py-3 bg-slate-50 rounded-xl outline-none font-bold border-2 border-transparent focus:border-blue-500">
                                <option value="">Pilih Karyawan...</option>
                                <option v-for="u in users" :key="u.id" :value="u.id">{{ u.name }}</option>
                            </select>
                            <p v-if="errors.assigned_to" class="text-rose-500 text-[10px] font-bold mt-1">{{ errors.assigned_to[0] }}</p>
                        </div>

                        <div class="space-y-1">
                            <label class="text-[10px] font-black text-slate-400 uppercase ml-1">Deskripsi</label>
                            <textarea v-model="taskForm.description" rows="3" class="w-full px-5 py-3 bg-slate-50 rounded-xl outline-none font-medium"></textarea>
                        </div>
                    </div>
                    <div class="mt-8 flex gap-3">
                        <button @click="saveTask" class="flex-1 bg-blue-600 text-white font-black py-4 rounded-2xl shadow-xl shadow-blue-100 hover:bg-blue-700">Simpan Task</button>
                        <button @click="closeTaskModal" class="px-8 bg-slate-100 text-slate-500 font-bold rounded-2xl">Batal</button>
                    </div>
                </div>
            </div>
        </transition>

        <!-- USER MODAL -->
        <transition name="modal">
            <div v-if="showUserModal" class="fixed inset-0 z-50 flex items-center justify-center p-4">
                <div class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm" @click="closeUserModal"></div>
                <div class="bg-white rounded-[32px] w-full max-w-md relative z-10 shadow-2xl p-8">
                    <h3 class="text-2xl font-black mb-6">{{ editingUser ? 'Edit Karyawan' : 'Tambah Karyawan' }}</h3>
                    <div class="space-y-4">
                        <div class="space-y-1">
                            <label class="text-[10px] font-black text-slate-400 uppercase ml-1">Nama Lengkap</label>
                            <input v-model="userForm.name" type="text" class="w-full px-5 py-3 bg-slate-50 rounded-xl outline-none font-bold">
                            <p v-if="errors.name" class="text-rose-500 text-[10px] font-bold mt-1">{{ errors.name[0] }}</p>
                        </div>
                        <div class="space-y-1">
                            <label class="text-[10px] font-black text-slate-400 uppercase ml-1">Email</label>
                            <input v-model="userForm.email" type="email" class="w-full px-5 py-3 bg-slate-50 rounded-xl outline-none font-bold">
                            <p v-if="errors.email" class="text-rose-500 text-[10px] font-bold mt-1">{{ errors.email[0] }}</p>
                        </div>
                        <div class="space-y-1">
                            <label class="text-[10px] font-black text-slate-400 uppercase ml-1">Password</label>
                            <input v-model="userForm.password" type="password" class="w-full px-5 py-3 bg-slate-50 rounded-xl outline-none font-bold">
                            <p v-if="errors.password" class="text-rose-500 text-[10px] font-bold mt-1">{{ errors.password[0] }}</p>
                        </div>
                    </div>
                    <div class="mt-8 flex gap-3">
                        <button @click="saveUser" class="flex-1 bg-blue-600 text-white font-black py-4 rounded-2xl shadow-xl shadow-blue-100 hover:bg-blue-700">Simpan Data</button>
                        <button @click="closeUserModal" class="px-8 bg-slate-100 text-slate-500 font-bold rounded-2xl">Batal</button>
                    </div>
                </div>
            </div>
        </transition>

        <!-- Toast -->
        <transition name="toast">
            <div v-if="toast.show" :class="['fixed bottom-8 left-1/2 -translate-x-1/2 px-8 py-4 rounded-2xl text-white font-bold shadow-2xl z-[100]', toast.type === 'error' ? 'bg-rose-500' : 'bg-slate-900']">
                {{ toast.message }}
            </div>
        </transition>
    </div>
</template>

<style>
@import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap');
body { font-family: 'Plus Jakarta Sans', sans-serif; }
.modal-enter-active, .modal-leave-active { transition: opacity 0.3s ease; }
.modal-enter-from, .modal-leave-to { opacity: 0; }
.toast-enter-active { animation: toast-in 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275); }
@keyframes toast-in { from { transform: translate(-50%, 100px); opacity: 0; } to { transform: translate(-50%, 0); opacity: 1; } }
</style>
