<template>
    <AppLayout>
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h4 class="mb-0">{{ project.name || 'Project Details' }}</h4>
            <div>
                <router-link to="/projects" class="btn btn-outline-secondary btn-sm me-1">
                    <i class="bi bi-arrow-left"></i> Back
                </router-link>
                <router-link v-if="canEdit" :to="'/projects/' + project.id + '/edit'" class="btn btn-primary btn-sm">
                    <i class="bi bi-pencil"></i> Edit
                </router-link>
            </div>
        </div>

        <div v-if="loading" class="text-center py-5"><div class="spinner-border"></div></div>

        <template v-else>
            <div class="row g-3">
                <div class="col-md-8">
                    <div class="card shadow-sm mb-3">
                        <div class="card-header bg-white"><h6 class="mb-0">Project Information</h6></div>
                        <div class="card-body">
                            <div class="mb-3">
                                <label class="fw-semibold text-muted small">Description</label>
                                <p class="mb-0">{{ project.description || 'No description provided.' }}</p>
                            </div>
                            <div class="row g-3">
                                <div class="col-sm-4">
                                    <label class="fw-semibold text-muted small">Status</label>
                                    <div><span class="badge" :class="statusBadge(project.status)">{{ project.status }}</span></div>
                                </div>
                                <div class="col-sm-4">
                                    <label class="fw-semibold text-muted small">Start Date</label>
                                    <div>{{ project.start_date || '—' }}</div>
                                </div>
                                <div class="col-sm-4">
                                    <label class="fw-semibold text-muted small">End Date</label>
                                    <div>{{ project.end_date || '—' }}</div>
                                </div>
                                <div class="col-sm-6">
                                    <label class="fw-semibold text-muted small">Project Manager</label>
                                    <div>{{ project.project_manager?.name || '—' }}</div>
                                </div>
                                <div class="col-sm-6">
                                    <label class="fw-semibold text-muted small">Total Tasks</label>
                                    <div>{{ project.tasks_count ?? 0 }}</div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card shadow-sm mb-3">
                        <div class="card-header bg-white d-flex justify-content-between align-items-center">
                            <h6 class="mb-0">Tasks ({{ project.tasks_count ?? 0 }})</h6>
                            <router-link v-if="canEdit" :to="'/tasks/create?project_id=' + project.id" class="btn btn-outline-primary btn-sm">
                                <i class="bi bi-plus-lg"></i> Add Task
                            </router-link>
                        </div>
                        <div v-if="tasks.length === 0" class="card-body text-center text-muted">No tasks for this project.</div>
                        <div v-else class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>Title</th>
                                        <th>Status</th>
                                        <th>Priority</th>
                                        <th>Assignee</th>
                                        <th>Deadline</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr v-for="task in tasks" :key="task.id">
                                        <td><router-link :to="'/tasks/' + task.id">{{ task.title }}</router-link></td>
                                        <td><span class="badge" :class="taskStatusBadge(task.status)">{{ task.status }}</span></td>
                                        <td><span class="badge" :class="priorityBadge(task.priority)">{{ task.priority }}</span></td>
                                        <td>{{ task.assigned_user?.name || '—' }}</td>
                                        <td>{{ task.deadline || '—' }}</td>
                                        <td>
                                            <router-link :to="'/tasks/' + task.id" class="btn btn-sm btn-outline-primary me-1" title="View">
                                                <i class="bi bi-eye"></i>
                                            </router-link>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="card shadow-sm mb-3">
                        <div class="card-header bg-white"><h6 class="mb-0">Progress</h6></div>
                        <div class="card-body text-center">
                            <div class="display-4 fw-bold" :class="progressColor">{{ progress.completion_percentage ?? 0 }}%</div>
                            <div class="progress mt-2" style="height: 10px;">
                                <div class="progress-bar" :class="progressColorBg" :style="'width: ' + (progress.completion_percentage ?? 0) + '%'"></div>
                            </div>
                            <div class="mt-3 text-start small">
                                <div v-for="(count, status) in progress.tasks_by_status" :key="status" class="d-flex justify-content-between mb-1">
                                    <span class="text-capitalize">{{ status.replace('_', ' ') }}</span>
                                    <span class="fw-semibold">{{ count }}</span>
                                </div>
                                <div class="d-flex justify-content-between pt-1 border-top">
                                    <span>Total</span>
                                    <span class="fw-bold">{{ progress.total_tasks ?? 0 }}</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card shadow-sm">
                        <div class="card-header bg-white"><h6 class="mb-0">Quick Actions</h6></div>
                        <div class="card-body d-grid gap-2">
                            <router-link :to="'/projects/' + project.id + '/edit'" class="btn btn-outline-primary btn-sm">
                                <i class="bi bi-pencil"></i> Edit Project
                            </router-link>
                            <button v-if="canArchive && project.status !== 'archived'" class="btn btn-outline-warning btn-sm" @click="archiveProject">
                                <i class="bi bi-archive"></i> Archive Project
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </template>
    </AppLayout>
</template>

<script setup>
import { ref, computed, onMounted, inject } from 'vue'
import { useRouter, useRoute } from 'vue-router'
import AppLayout from '../../components/AppLayout.vue'
import api from '../../composables/useApi'

const router = useRouter()
const route = useRoute()
const flash = inject('flash')
const loading = ref(true)
const project = ref({})
const progress = ref({})
const tasks = ref([])
const canEdit = ref(false)
const canArchive = ref(false)

const progressColor = computed(() => {
    const p = progress.value.completion_percentage ?? 0
    if (p >= 100) return 'text-success'
    if (p >= 50) return 'text-primary'
    return 'text-warning'
})

const progressColorBg = computed(() => {
    const p = progress.value.completion_percentage ?? 0
    if (p >= 100) return 'bg-success'
    if (p >= 50) return 'bg-primary'
    return 'bg-warning'
})

function statusBadge(status) {
    const map = { planning: 'bg-secondary', active: 'bg-primary', completed: 'bg-success', archived: 'bg-dark' }
    return map[status] || 'bg-secondary'
}

function taskStatusBadge(status) {
    const map = { todo: 'bg-secondary', in_progress: 'bg-primary', completed: 'bg-success', cancelled: 'bg-danger' }
    return map[status] || 'bg-secondary'
}

function priorityBadge(priority) {
    const map = { low: 'bg-success', medium: 'bg-warning text-dark', high: 'bg-danger', critical: 'bg-dark' }
    return map[priority] || 'bg-secondary'
}

onMounted(async () => {
    try {
        const [projectRes, progressRes, tasksRes] = await Promise.all([
            api.get('/projects/' + route.params.id),
            api.get('/projects/' + route.params.id + '/progress'),
            api.get('/tasks', { params: { project_id: route.params.id, per_page: 50 } }),
        ])
        project.value = projectRes.data.data
        progress.value = progressRes.data.data
        tasks.value = tasksRes.data.data

        const user = JSON.parse(localStorage.getItem('user') || '{}')
        const role = user.roles?.[0]?.name
        canEdit.value = role === 'admin' || (role === 'project-manager' && project.value.project_manager_id === user.id)
        canArchive.value = role === 'admin' || role === 'project-manager'
    } catch (e) {
        console.error(e)
        flash('Failed to load project details', 'danger')
        router.push('/projects')
    } finally {
        loading.value = false
    }
})

async function archiveProject() {
    if (!confirm('Archive this project?')) return
    try {
        await api.post('/projects/' + route.params.id + '/archive')
        flash('Project archived successfully')
        router.push('/projects')
    } catch (e) {
        flash(e.response?.data?.message || 'Error archiving project', 'danger')
    }
}
</script>
