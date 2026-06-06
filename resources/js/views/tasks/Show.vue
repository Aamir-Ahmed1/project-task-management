<template>
    <AppLayout>
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h4 class="mb-0">{{ task.name || 'Task Details' }}</h4>
            <div>
                <router-link to="/tasks" class="btn btn-outline-secondary btn-sm me-1">
                    <i class="bi bi-arrow-left"></i> Back
                </router-link>
                <router-link v-if="canEdit" :to="'/tasks/' + route.params.id + '/edit'" class="btn btn-primary btn-sm">
                    <i class="bi bi-pencil"></i> Edit
                </router-link>
            </div>
        </div>

        <div v-if="loading" class="text-center py-5"><div class="spinner-border"></div></div>

        <template v-else>
            <div class="row g-3">
                <div class="col-md-8">
                    <div class="card shadow-sm mb-3">
                        <div class="card-header bg-white"><h6 class="mb-0">Task Information</h6></div>
                        <div class="card-body">
                            <div class="mb-3">
                                <label class="fw-semibold text-muted small">Description</label>
                                <p class="mb-0">{{ task.description || 'No description provided.' }}</p>
                            </div>
                            <div class="row g-3">
                                <div class="col-sm-4">
                                    <label class="fw-semibold text-muted small">Priority</label>
                                    <div><span class="badge" :class="priorityBadge(task.priority)">{{ task.priority }}</span></div>
                                </div>
                                <div class="col-sm-4">
                                    <label class="fw-semibold text-muted small">Status</label>
                                    <div><span class="badge" :class="statusBadge(task.status)">{{ task.status.replace('_', ' ') }}</span></div>
                                </div>
                                <div class="col-sm-4">
                                    <label class="fw-semibold text-muted small">Project</label>
                                    <div>{{ task.project?.name || '—' }}</div>
                                </div>
                                <div class="col-sm-4">
                                    <label class="fw-semibold text-muted small">Assigned To</label>
                                    <div>{{ task.assigned_user?.name || '—' }}</div>
                                </div>
                                <div class="col-sm-4">
                                    <label class="fw-semibold text-muted small">Deadline</label>
                                    <div>
                                        <span v-if="task.deadline">
                                            {{ task.deadline }}
                                            <span v-if="isOverdue" class="badge bg-danger ms-1">Overdue</span>
                                        </span>
                                        <span v-else>—</span>
                                    </div>
                                </div>
                                <div class="col-sm-4">
                                    <label class="fw-semibold text-muted small">Created By</label>
                                    <div>{{ task.creator?.name || '—' }}</div>
                                </div>
                                <div class="col-sm-4">
                                    <label class="fw-semibold text-muted small">Estimated Hours</label>
                                    <div>{{ task.estimated_hours ?? '—' }}</div>
                                </div>
                                <div class="col-sm-4">
                                    <label class="fw-semibold text-muted small">Actual Hours</label>
                                    <div>{{ task.actual_hours ?? '—' }}</div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card shadow-sm mb-3">
                        <div class="card-header bg-white"><h6 class="mb-0">Work Logs</h6></div>
                        <div v-if="workLogs.length === 0" class="card-body text-center text-muted">No work logs recorded.</div>
                        <div v-else>
                            <div v-for="log in workLogs" :key="log.id" class="border-bottom p-3">
                                <div class="d-flex justify-content-between align-items-start">
                                    <div>
                                        <strong>{{ log.user?.name || 'Unknown' }}</strong>
                                        <small class="text-muted ms-2">{{ log.logged_at || log.created_at }}</small>
                                    </div>
                                    <span class="badge bg-info text-dark">{{ log.hours_worked }}h</span>
                                </div>
                                <p class="mb-1 mt-1">{{ log.description }}</p>
                                <div v-if="log.attachment" class="mb-1">
                                    <a :href="log.attachment" target="_blank" class="small"><i class="bi bi-paperclip"></i> Attachment</a>
                                </div>

                                <div v-if="expandedReplies[log.id]" class="mt-2 ms-3 border-start ps-3">
                                    <div v-if="loadingReplies[log.id]" class="small text-muted">Loading...</div>
                                    <div v-else-if="replies[log.id]?.length">
                                        <div v-for="reply in replies[log.id]" :key="reply.id" class="mb-2">
                                            <strong class="small">{{ reply.user?.name }}</strong>
                                            <small class="text-muted ms-1">{{ reply.created_at }}</small>
                                            <p class="mb-0 small">{{ reply.reply }}</p>
                                        </div>
                                    </div>
                                    <div v-else class="small text-muted mb-2">No replies yet.</div>
                                    <form v-if="canReply" class="d-flex gap-2 mt-2" @submit.prevent="submitReply(log.id)">
                                        <input v-model="replyForms[log.id]" class="form-control form-control-sm" placeholder="Add a reply..." required>
                                        <button type="submit" class="btn btn-outline-primary btn-sm" :disabled="savingReply === log.id">
                                            <span v-if="savingReply === log.id" class="spinner-border spinner-border-sm"></span>
                                            <span v-else>Reply</span>
                                        </button>
                                    </form>
                                </div>

                                <button v-if="canReply || canViewReplies" class="btn btn-sm btn-link p-0 mt-1" @click="toggleReplies(log.id)">
                                    {{ expandedReplies[log.id] ? 'Hide replies' : 'View replies' }}
                                </button>
                            </div>
                        </div>
                    </div>

                    <div v-if="canLogWork" class="card shadow-sm mb-3">
                        <div class="card-header bg-white"><h6 class="mb-0">Log Work</h6></div>
                        <div class="card-body">
                            <form @submit.prevent="submitWorkLog">
                                <div class="row g-2">
                                    <div class="col-12">
                                        <textarea v-model="workLogForm.description" class="form-control form-control-sm" rows="2" placeholder="What did you work on?" :class="{ 'is-invalid': workLogErrors.description }" required></textarea>
                                        <div v-if="workLogErrors.description" class="invalid-feedback">{{ workLogErrors.description }}</div>
                                    </div>
                                    <div class="col-md-4">
                                        <input v-model="workLogForm.hours_worked" type="number" step="0.25" min="0.25" max="24" class="form-control form-control-sm" placeholder="Hours" :class="{ 'is-invalid': workLogErrors.hours_worked }" required>
                                        <div v-if="workLogErrors.hours_worked" class="invalid-feedback">{{ workLogErrors.hours_worked }}</div>
                                    </div>
                                    <div class="col-md-4">
                                        <input v-model="workLogForm.logged_at" type="date" class="form-control form-control-sm" :class="{ 'is-invalid': workLogErrors.logged_at }">
                                        <div v-if="workLogErrors.logged_at" class="invalid-feedback">{{ workLogErrors.logged_at }}</div>
                                    </div>
                                    <div class="col-md-4 d-grid">
                                        <button type="submit" class="btn btn-primary btn-sm" :disabled="savingWorkLog">
                                            <span v-if="savingWorkLog" class="spinner-border spinner-border-sm"></span>
                                            <span v-else>Log Time</span>
                                        </button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>

                    <div class="card shadow-sm mb-3">
                        <div class="card-header bg-white"><h6 class="mb-0">Activity Timeline</h6></div>
                        <div v-if="timelineLoading" class="card-body text-center"><div class="spinner-border spinner-border-sm"></div></div>
                        <div v-else-if="timeline.length === 0" class="card-body text-center text-muted">No activity recorded.</div>
                        <div v-else class="card-body p-0">
                            <div class="list-group list-group-flush">
                                <div v-for="entry in timeline" :key="entry.id" class="list-group-item py-2">
                                    <div class="d-flex justify-content-between">
                                        <strong class="small">{{ entry.user || 'System' }}</strong>
                                        <small class="text-muted">{{ entry.occurred_at }}</small>
                                    </div>
                                    <p class="mb-0 small">{{ entry.action }}</p>
                                    <div v-if="entry.previous_values || entry.new_values" class="mt-1 small text-muted">
                                        <template v-if="entry.previous_values">
                                            <span class="text-danger">{{ JSON.stringify(entry.previous_values) }}</span>
                                        </template>
                                        <template v-if="entry.new_values">
                                            <span v-if="entry.previous_values"> → </span>
                                            <span class="text-success">{{ JSON.stringify(entry.new_values) }}</span>
                                        </template>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="card shadow-sm mb-3">
                        <div class="card-header bg-white"><h6 class="mb-0">Update Status</h6></div>
                        <div class="card-body">
                            <div class="d-grid gap-2">
                                <button v-for="s in availableStatuses" :key="s.value" class="btn btn-sm" :class="status === s.value ? 'btn-primary' : 'btn-outline-secondary'" :disabled="task.status === s.value || updatingStatus" @click="changeStatus(s.value)">
                                    <span v-if="updatingStatus === s.value" class="spinner-border spinner-border-sm me-1"></span>
                                    {{ s.label }}
                                </button>
                            </div>
                        </div>
                    </div>

                    <div class="card shadow-sm">
                        <div class="card-header bg-white"><h6 class="mb-0">Quick Actions</h6></div>
                        <div class="card-body d-grid gap-2">
                            <router-link v-if="canEdit" :to="'/tasks/' + route.params.id + '/edit'" class="btn btn-outline-primary btn-sm">
                                <i class="bi bi-pencil"></i> Edit Task
                            </router-link>
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

const user = JSON.parse(localStorage.getItem('user') || '{}')
const role = user.roles?.[0]
const userId = user.id

const loading = ref(true)
const task = ref({})
const workLogs = ref([])
const timeline = ref([])
const timelineLoading = ref(false)
const canEdit = ref(false)
const canLogWork = ref(false)
const canReply = ref(false)
const canViewReplies = ref(false)
const updatingStatus = ref(false)

const expandedReplies = ref({})
const loadingReplies = ref({})
const replies = ref({})
const replyForms = ref({})
const savingReply = ref(null)

const workLogForm = ref({ description: '', hours_worked: '', logged_at: '' })
const workLogErrors = ref({})
const savingWorkLog = ref(false)

const availableStatuses = [
    { value: 'pending', label: 'Pending' },
    { value: 'in_progress', label: 'In Progress' },
    { value: 'review', label: 'Review' },
    { value: 'completed', label: 'Completed' },
]

const status = computed(() => task.value.status)

const isOverdue = computed(() => {
    if (task.value.status === 'completed') return false
    if (!task.value.deadline) return false
    return new Date(task.value.deadline) < new Date()
})

function priorityBadge(priority) {
    const map = { low: 'bg-success', medium: 'bg-warning text-dark', high: 'bg-danger', critical: 'bg-dark' }
    return map[priority] || 'bg-secondary'
}

function statusBadge(status) {
    const map = { pending: 'bg-secondary', in_progress: 'bg-primary', review: 'bg-info text-dark', completed: 'bg-success' }
    return map[status] || 'bg-secondary'
}

async function fetchTask() {
    try {
        const res = await api.get('/tasks/' + route.params.id)
        task.value = res.data.data
        workLogs.value = res.data.data.work_logs || []

        canEdit.value = role === 'admin' || (role === 'project-manager' && task.value.project?.project_manager_id === userId)
        canLogWork.value = role === 'employee' && task.value.assigned_to === userId
        canReply.value = role === 'admin' || role === 'project-manager'
        canViewReplies.value = true
    } catch (e) {
        flash('Failed to load task', 'danger')
        router.push('/tasks')
    } finally {
        loading.value = false
    }
}

async function fetchTimeline() {
    timelineLoading.value = true
    try {
        const res = await api.get('/tasks/' + route.params.id + '/timeline')
        timeline.value = res.data.data || []
    } catch (e) {
        console.error(e)
    } finally {
        timelineLoading.value = false
    }
}

async function changeStatus(newStatus) {
    updatingStatus.value = newStatus
    try {
        await api.patch('/tasks/' + route.params.id + '/status', { status: newStatus })
        task.value.status = newStatus
        flash('Status updated successfully')
    } catch (e) {
        flash(e.response?.data?.message || 'Error updating status', 'danger')
    } finally {
        updatingStatus.value = false
    }
}

async function toggleReplies(logId) {
    if (expandedReplies.value[logId]) {
        expandedReplies.value[logId] = false
        return
    }
    expandedReplies.value[logId] = true
    loadingReplies.value[logId] = true
    try {
        const res = await api.get('/work-logs/' + logId + '/replies')
        replies.value[logId] = res.data.data || []
    } catch (e) {
        console.error(e)
    } finally {
        loadingReplies.value[logId] = false
    }
}

async function submitReply(logId) {
    const text = replyForms.value[logId]
    if (!text?.trim()) return
    savingReply.value = logId
    try {
        await api.post('/work-logs/' + logId + '/replies', { reply: text })
        replyForms.value[logId] = ''
        flash('Reply added')
        if (expandedReplies.value[logId]) {
            const res = await api.get('/work-logs/' + logId + '/replies')
            replies.value[logId] = res.data.data || []
        }
    } catch (e) {
        flash(e.response?.data?.message || 'Error adding reply', 'danger')
    } finally {
        savingReply.value = null
    }
}

async function submitWorkLog() {
    savingWorkLog.value = true
    workLogErrors.value = {}
    try {
        await api.post('/tasks/' + route.params.id + '/work-logs', workLogForm.value)
        flash('Work log created successfully')
        workLogForm.value = { description: '', hours_worked: '', logged_at: '' }
        const res = await api.get('/tasks/' + route.params.id)
        task.value = res.data.data
        workLogs.value = res.data.data.work_logs || []
    } catch (e) {
        if (e.response?.status === 422) {
            const errs = e.response.data.errors
            const flat = {}
            for (const key in errs) flat[key] = Array.isArray(errs[key]) ? errs[key][0] : errs[key]
            workLogErrors.value = flat
        } else {
            flash(e.response?.data?.message || 'Error logging work', 'danger')
        }
    } finally {
        savingWorkLog.value = false
    }
}

onMounted(() => {
    fetchTask()
    fetchTimeline()
})
</script>
