<script setup>
import { ref, onMounted, computed } from 'vue';
import axios from 'axios';

// State
const tasks = ref([]);
const users = ref([]);
const isLoading = ref(false);
const filter = ref('all');
const activeMenu = ref('Tasklist'); // 'Tasklist' or 'Employees'

// Modals
const showTaskModal = ref(false);
const showUserModal = ref(false);
const editingTask = ref(null);
const editingUser = ref(null);

// Form States
const taskForm = ref({ title: '', description: '', start_date: '', end_date: '', assigned_to: '' });
const userForm = ref({ name: '', email: '', password: '' });
const toast = ref({ show: false, message: '', type: 'success' });

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
        showToast(error.response?.data?.message || 'Gagal menyimpan tugas', 'error');
    }
};

const toggleTaskStatus = async (task) => {
    try {
        const res = await axios.put(`/api/v1/tasks/${task.id}`, { is_completed: !task.is_completed });
        const index = tasks.value.findIndex(t => t.id === task.id);
        tasks.value[index] = res.data.data;
        showToast(task.is_completed ? 'Tugas belum selesai' : 'Tugas selesai!');
    } catch (error) {
        showToast('Gagal update status', 'error');
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
        fetchData(); // Refresh to update assignees in tasks
    } catch (error) {
        showToast(error.response?.data?.message || 'Gagal menyimpan karyawan', 'error');
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
const openTaskModal = (task = null) => {
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
            <!-- Sidebar Left (Menu) -->
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

                <!-- Company Info Card -->
                <div class="bg-slate-900 rounded-[24px] p-6 text-white relative overflow-hidden">
                    <div class="relative z-10">
                        <p class="text-blue-400 text-[10px] font-black uppercase mb-1">Company Summary</p>
                        <h4 class="text-xl font-bold mb-4">Efisiensi Kerja</h4>
                        <div class="space-y-4">
                            <div>
                                <div class="flex justify-between text-[10px] font-bold mb-1"><span>Tasks Done</span><span>{{ Math.round((tasks.filter(t=>t.is_completed).length / (tasks.length || 1)) * 100) }}%</span></div>
                                <div class="h-1.5 bg-slate-800 rounded-full overflow-hidden"><div class="h-full bg-blue-500" :style="{width: `${(tasks.filter(t=>t.is_completed).length / (tasks.length || 1)) * 100}%`}"></div></div>
                            </div>
                        </div>
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
                            <p class="text-sm text-slate-500">Daftar pekerjaan internal Sinar Roda Utama</p>
                        </div>
                        <button @click="openTaskModal()" class="px-6 py-3 bg-blue-600 text-white font-black rounded-2xl shadow-xl shadow-blue-100 hover:-translate-y-1 transition-all active:scale-95 text-sm">+ Tambah Task</button>
                    </div>

                    <!-- Filter & Task Cards -->
                    <div class="flex gap-2 pb-2 border-b border-slate-200">
                        <button v-for="t in ['all', 'active', 'completed']" :key="t" @click="filter = t"
                            :class="['px-5 py-2 rounded-full text-xs font-bold capitalize transition-all', filter === t ? 'bg-slate-900 text-white' : 'bg-white text-slate-500 hover:bg-slate-100']">
                            {{ t }}
                        </button>
                    </div>

                    <div class="grid grid-cols-1 gap-4">
                        <div v-for="task in filteredTasks" :key="task.id" class="bg-white border border-slate-200 rounded-2xl p-5 hover:shadow-xl transition-all group">
                            <div class="flex items-start gap-4">
                                <button @click="toggleTaskStatus(task)" :class="['w-6 h-6 rounded-lg border-2 flex-shrink-0 mt-1', task.is_completed ? 'bg-emerald-500 border-emerald-500 text-white' : 'border-slate-200 text-transparent']">✓</button>
                                <div class="flex-1 min-w-0">
                                    <div class="flex items-center gap-2 mb-1">
                                        <h3 :class="['font-bold text-lg truncate', task.is_completed ? 'text-slate-400 line-through' : 'text-slate-800']">{{ task.title }}</h3>
                                    </div>
                                    <p class="text-sm text-slate-500 mb-4 line-clamp-2">{{ task.description || 'No description' }}</p>

                                    <div class="flex flex-wrap items-center gap-4 text-[11px] font-bold">
                                        <span class="flex items-center text-slate-400 bg-slate-50 px-2 py-1 rounded-md">📅 {{ task.start_date || '-' }} s/d {{ task.end_date || '-' }}</span>
                                        <span v-if="task.assignee" class="flex items-center text-blue-600 bg-blue-50 px-2 py-1 rounded-md">👤 {{ task.assignee.name }}</span>
                                        <span class="ml-auto text-slate-300">Created: {{ task.created_at }}</span>
                                    </div>
                                </div>
                                <div class="flex flex-col gap-2 opacity-0 group-hover:opacity-100 transition-opacity">
                                    <button @click="openTaskModal(task)" class="p-2 text-slate-400 hover:text-blue-600 hover:bg-blue-50 rounded-xl">✏️</button>
                                    <button @click="deleteTask(task.id)" class="p-2 text-slate-400 hover:text-red-600 hover:bg-red-50 rounded-xl">🗑️</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- EMPLOYEES VIEW -->
                <div v-if="activeMenu === 'Employees'" class="space-y-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <h2 class="text-2xl font-black text-slate-900">Database Karyawan</h2>
                            <p class="text-sm text-slate-500">Manajemen data tim PT. Sinar Roda Utama</p>
                        </div>
                        <button @click="openUserModal()" class="px-6 py-3 bg-blue-600 text-white font-black rounded-2xl shadow-xl shadow-blue-100 hover:-translate-y-1 transition-all active:scale-95 text-sm">+ Tambah Karyawan</button>
                    </div>

                    <div class="bg-white border border-slate-200 rounded-[24px] overflow-hidden shadow-sm">
                        <table class="w-full text-left text-sm">
                            <thead class="bg-slate-50 border-b border-slate-200">
                                <tr>
                                    <th class="px-6 py-4 font-black text-slate-400 uppercase tracking-widest text-[10px]">Nama Karyawan</th>
                                    <th class="px-6 py-4 font-black text-slate-400 uppercase tracking-widest text-[10px]">Email</th>
                                    <th class="px-6 py-4 font-black text-slate-400 uppercase tracking-widest text-[10px]">Tgl Bergabung</th>
                                    <th class="px-6 py-4"></th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                <tr v-for="user in users" :key="user.id" class="hover:bg-slate-50 transition-colors group">
                                    <td class="px-6 py-4">
                                        <div class="flex items-center gap-3">
                                            <img :src="user.avatar" class="w-8 h-8 rounded-full">
                                            <span class="font-bold text-slate-800">{{ user.name }}</span>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 font-medium text-slate-500">{{ user.email }}</td>
                                    <td class="px-6 py-4 font-medium text-slate-500">{{ user.created_at }}</td>
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

        <!-- TASK MODAL -->
        <transition name="modal">
            <div v-if="showTaskModal" class="fixed inset-0 z-50 flex items-center justify-center p-4">
                <div class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm" @click="closeTaskModal"></div>
                <div class="bg-white rounded-[32px] w-full max-w-lg relative z-10 shadow-2xl p-8">
                    <h3 class="text-2xl font-black mb-6">{{ editingTask ? 'Edit Task' : 'Tambah Task' }}</h3>
                    <div class="space-y-4">
                        <div class="grid grid-cols-2 gap-4">
                            <div class="space-y-1 col-span-2">
                                <label class="text-[10px] font-black text-slate-400 uppercase ml-1">Judul Tugas</label>
                                <input v-model="taskForm.title" type="text" class="w-full px-5 py-3 bg-slate-50 rounded-xl focus:bg-white border-2 border-transparent focus:border-blue-500 outline-none font-bold">
                            </div>
                            <div class="space-y-1">
                                <label class="text-[10px] font-black text-slate-400 uppercase ml-1">Tgl Mulai</label>
                                <input v-model="taskForm.start_date" type="date" class="w-full px-5 py-3 bg-slate-50 rounded-xl outline-none font-bold">
                            </div>
                            <div class="space-y-1">
                                <label class="text-[10px] font-black text-slate-400 uppercase ml-1">Tgl Selesai</label>
                                <input v-model="taskForm.end_date" type="date" class="w-full px-5 py-3 bg-slate-50 rounded-xl outline-none font-bold">
                            </div>
                            <div class="space-y-1 col-span-2">
                                <label class="text-[10px] font-black text-slate-400 uppercase ml-1">Ditujukan Kepada</label>
                                <select v-model="taskForm.assigned_to" class="w-full px-5 py-3 bg-slate-50 rounded-xl outline-none font-bold">
                                    <option value="">Pilih Karyawan...</option>
                                    <option v-for="u in users" :key="u.id" :value="u.id">{{ u.name }}</option>
                                </select>
                            </div>
                            <div class="space-y-1 col-span-2">
                                <label class="text-[10px] font-black text-slate-400 uppercase ml-1">Deskripsi</label>
                                <textarea v-model="taskForm.description" rows="3" class="w-full px-5 py-3 bg-slate-50 rounded-xl outline-none font-medium"></textarea>
                            </div>
                        </div>
                    </div>
                    <div class="mt-8 flex gap-3">
                        <button @click="saveTask" class="flex-1 bg-blue-600 text-white font-black py-4 rounded-2xl shadow-xl shadow-blue-100 transition-all hover:bg-blue-700">Simpan Task</button>
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
                        </div>
                        <div class="space-y-1">
                            <label class="text-[10px] font-black text-slate-400 uppercase ml-1">Email</label>
                            <input v-model="userForm.email" type="email" class="w-full px-5 py-3 bg-slate-50 rounded-xl outline-none font-bold">
                        </div>
                        <div class="space-y-1">
                            <label class="text-[10px] font-black text-slate-400 uppercase ml-1">Password</label>
                            <input v-model="userForm.password" type="password" placeholder="Min 8 karakter" class="w-full px-5 py-3 bg-slate-50 rounded-xl outline-none font-bold">
                        </div>
                    </div>
                    <div class="mt-8 flex gap-3">
                        <button @click="saveUser" class="flex-1 bg-blue-600 text-white font-black py-4 rounded-2xl shadow-xl shadow-blue-100 transition-all hover:bg-blue-700">Simpan Data</button>
                        <button @click="closeUserModal" class="px-8 bg-slate-100 text-slate-500 font-bold rounded-2xl">Batal</button>
                    </div>
                </div>
            </div>
        </transition>

        <!-- Toast -->
        <transition name="toast">
            <div v-if="toast.show" :class="['fixed bottom-8 left-1/2 -translate-x-1/2 px-8 py-4 rounded-2xl text-white font-bold shadow-2xl z-[100] transition-all', toast.type === 'error' ? 'bg-rose-500' : 'bg-slate-900']">
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
