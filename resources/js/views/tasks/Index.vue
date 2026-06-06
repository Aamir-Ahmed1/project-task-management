<template>
    <AppLayout>
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h4 class="mb-0">Tasks</h4>
            <router-link v-if="canCreate" to="/tasks/create" class="btn btn-primary btn-sm">
                <i class="bi bi-plus-lg"></i> Create Task
            </router-link>
        </div>

        <div class="card shadow-sm mb-3">
            <div class="card-body">
                <div class="row g-2">
                    <div class="col-md-3">
                        <select v-model="filters.status" class="form-select form-select-sm" @change="fetchTasks(1)">
                            <option value="">All Status</option>
                            <option value="pending">Pending</option>
                            <option value="in_progress">In Progress</option>
                            <option value="review">Review</option>
                            <option value="completed">Completed</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <select v-model="filters.priority" class="form-select form-select-sm" @change="fetchTasks(1)">
                            <option value="">All Priority</option>
                            <option value="low">Low</option>
                            <option value="medium">Medium</option>
                            <option value="high">High</option>
                            <option value="critical">Critical</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <input v-model="filters.search" class="form-control form-control-sm" placeholder="Search tasks..." @keyup.enter="fetchTasks(1)">
                    </div>
                    <div class="col-md-2">
                        <button class="btn btn-outline-secondary btn-sm w-100" @click="fetchTasks(1)">Filter</button>
                    </div>
                </div>
            </div>
        </div>

        <div v-if="loading" class="text-center py-5"><div class="spinner-border"></div></div>

        <div v-else-if="tasks.length === 0" class="text-center py-5 text-muted">No tasks found</div>

        <template v-else>
            <div class="card shadow-sm">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Name</th>
                                <th>Project</th>
                                <th>Priority</th>
                                <th>Status</th>
                                <th>Assigned To</th>
                                <th>Deadline</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="task in tasks" :key="task.id">
                                <td><router-link :to="'/tasks/' + task.id">{{ task.name }}</router-link></td>
                                <td>{{ task.project?.name || '—' }}</td>
                                <td><span class="badge" :class="priorityBadge(task.priority)">{{ task.priority }}</span></td>
                                <td><span class="badge" :class="statusBadge(task.status)">{{ task.status.replace('_', ' ') }}</span></td>
                                <td>{{ task.assigned_user?.name || '—' }}</td>
                                <td>
                                    <span v-if="task.deadline">
                                        {{ task.deadline }}
                                        <span v-if="isOverdue(task)" class="badge bg-danger ms-1">Overdue</span>
                                    </span>
                                    <span v-else>—</span>
                                </td>
                                <td>
                                    <div class="btn-group btn-group-sm">
                                        <router-link :to="'/tasks/' + task.id" class="btn btn-outline-primary" title="View"><i class="bi bi-eye"></i></router-link>
                                        <router-link :to="'/tasks/' + task.id + '/edit'" class="btn btn-outline-secondary" title="Edit"><i class="bi bi-pencil"></i></router-link>
                                        <button v-if="canDelete" class="btn btn-outline-danger" title="Delete" @click="deleteTask(task.id)"><i class="bi bi-trash"></i></button>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
            <nav v-if="meta.last_page > 1" class="mt-3">
                <ul class="pagination pagination-sm justify-content-center">
                    <li class="page-item" :class="{ disabled: meta.current_page === 1 }">
                        <button class="page-link" @click="fetchTasks(meta.current_page - 1)">Previous</button>
                    </li>
                    <li v-for="page in meta.last_page" :key="page" class="page-item" :class="{ active: meta.current_page === page }">
                        <button class="page-link" @click="fetchTasks(page)">{{ page }}</button>
                    </li>
                    <li class="page-item" :class="{ disabled: meta.current_page === meta.last_page }">
                        <button class="page-link" @click="fetchTasks(meta.current_page + 1)">Next</button>
                    </li>
                </ul>
            </nav>
        </template>
    </AppLayout>
</template>

<script setup>
import { ref, onMounted, inject } from 'vue'
import AppLayout from '../../components/AppLayout.vue'
import api from '../../composables/useApi'

const tasks = ref([])
const meta = ref({ current_page: 1, last_page: 1 })
const loading = ref(true)
const filters = ref({ status: '', priority: '', search: '' })
const canCreate = ref(false)
const canDelete = ref(false)
const flash = inject('flash')

function isOverdue(task) {
    if (task.status === 'completed') return false
    if (!task.deadline) return false
    return new Date(task.deadline) < new Date()
}

async function fetchTasks(page = 1) {
    loading.value = true
    try {
        const params = { ...filters.value, page }
        const res = await api.get('/tasks', { params })
        tasks.value = res.data.data
        meta.value = res.data.meta || { current_page: 1, last_page: 1 }
        const user = JSON.parse(localStorage.getItem('user') || '{}')
        const role = user.roles?.[0]
        canCreate.value = role === 'admin' || role === 'project-manager'
        canDelete.value = role === 'admin'
    } catch (e) {
        console.error(e)
        flash('Failed to load tasks', 'danger')
    } finally {
        loading.value = false
    }
}

async function deleteTask(id) {
    if (!confirm('Delete this task permanently?')) return
    try {
        await api.delete('/tasks/' + id)
        flash('Task deleted successfully')
        fetchTasks(meta.value.current_page)
    } catch (e) {
        flash(e.response?.data?.message || 'Error deleting task', 'danger')
    }
}

function priorityBadge(priority) {
    const map = { low: 'bg-success', medium: 'bg-warning text-dark', high: 'bg-danger', critical: 'bg-dark' }
    return map[priority] || 'bg-secondary'
}

function statusBadge(status) {
    const map = { pending: 'bg-secondary', in_progress: 'bg-primary', review: 'bg-info text-dark', completed: 'bg-success' }
    return map[status] || 'bg-secondary'
}

onMounted(() => fetchTasks(1))
</script>
