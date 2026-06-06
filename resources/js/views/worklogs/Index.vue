<template>
    <AppLayout>
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h4 class="mb-0">Work Logs</h4>
            <router-link :to="'/tasks/' + route.params.taskId" class="btn btn-outline-secondary btn-sm">
                <i class="bi bi-arrow-left"></i> Back to Task
            </router-link>
        </div>

        <div v-if="loading" class="text-center py-5"><div class="spinner-border"></div></div>

        <template v-else>
            <div v-if="task" class="card shadow-sm mb-3">
                <div class="card-body py-2">
                    <strong>{{ task.name }}</strong>
                    <span v-if="task.project" class="text-muted ms-2">— {{ task.project.name }}</span>
                    <span class="badge ms-2" :class="statusBadge(task.status)">{{ task.status }}</span>
                </div>
            </div>

            <div v-if="canLogWork" class="card shadow-sm mb-3">
                <div class="card-header bg-white"><h6 class="mb-0">Log Work</h6></div>
                <div class="card-body">
                    <form @submit.prevent="submitWorkLog">
                        <div class="mb-2">
                            <textarea v-model="form.description" class="form-control form-control-sm" rows="2" placeholder="What did you work on?" :class="{ 'is-invalid': errors.description }" required></textarea>
                            <div v-if="errors.description" class="invalid-feedback">{{ errors.description }}</div>
                        </div>
                        <div class="row g-2">
                            <div class="col-md-3">
                                <input v-model="form.hours_worked" type="number" step="0.25" min="0.25" max="24" class="form-control form-control-sm" placeholder="Hours" :class="{ 'is-invalid': errors.hours_worked }" required>
                                <div v-if="errors.hours_worked" class="invalid-feedback">{{ errors.hours_worked }}</div>
                            </div>
                            <div class="col-md-4">
                                <input ref="fileInput" type="file" class="form-control form-control-sm" @change="onFileChange">
                            </div>
                            <div class="col-md-3">
                                <input v-model="form.logged_at" type="date" class="form-control form-control-sm">
                            </div>
                            <div class="col-md-2 d-grid">
                                <button type="submit" class="btn btn-primary btn-sm" :disabled="saving">
                                    <span v-if="saving" class="spinner-border spinner-border-sm"></span>
                                    <span v-else>Log Time</span>
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <div v-if="workLogs.length === 0" class="text-center py-5 text-muted">No work logs recorded for this task.</div>

            <div v-else class="card shadow-sm">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>User</th>
                                <th>Description</th>
                                <th>Hours</th>
                                <th>Date</th>
                                <th>Attachment</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="log in workLogs" :key="log.id">
                                <td>{{ log.user?.name || 'Unknown' }}</td>
                                <td>{{ log.description }}</td>
                                <td><span class="badge bg-info text-dark">{{ log.hours_worked }}h</span></td>
                                <td><small class="text-muted">{{ log.logged_at || log.created_at }}</small></td>
                                <td>
                                    <a v-if="log.attachment" :href="log.attachment" target="_blank" class="small"><i class="bi bi-paperclip"></i> View</a>
                                    <span v-else class="text-muted small">—</span>
                                </td>
                                <td>
                                    <button v-if="canReply" class="btn btn-sm btn-link p-0" @click="toggleReplies(log.id)">
                                        {{ expandedReplies[log.id] ? 'Hide replies' : 'Replies' }}
                                    </button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <div v-if="expandedLog" class="border-top p-3 bg-light">
                    <div v-if="loadingReplies" class="small text-muted">Loading replies...</div>
                    <template v-else>
                        <div v-if="replies.length === 0" class="small text-muted mb-2">No replies yet.</div>
                        <div v-for="reply in replies" :key="reply.id" class="mb-2">
                            <strong class="small">{{ reply.user?.name }}</strong>
                            <small class="text-muted ms-1">{{ reply.created_at }}</small>
                            <p class="mb-0 small">{{ reply.reply }}</p>
                        </div>
                        <form v-if="canReply" class="d-flex gap-2 mt-2" @submit.prevent="submitReply">
                            <textarea v-model="replyText" class="form-control form-control-sm" rows="1" placeholder="Write a reply..." required></textarea>
                            <button type="submit" class="btn btn-outline-primary btn-sm" :disabled="savingReply">
                                <span v-if="savingReply" class="spinner-border spinner-border-sm"></span>
                                <span v-else>Send</span>
                            </button>
                        </form>
                    </template>
                </div>
            </div>
        </template>
    </AppLayout>
</template>

<script setup>
import { ref, onMounted, inject } from 'vue'
import { useRoute } from 'vue-router'
import AppLayout from '../../components/AppLayout.vue'
import api from '../../composables/useApi'

const route = useRoute()
const flash = inject('flash')

const task = ref(null)
const workLogs = ref([])
const loading = ref(true)
const canLogWork = ref(false)
const canReply = ref(false)

const form = ref({ description: '', hours_worked: '', logged_at: '' })
const errors = ref({})
const saving = ref(false)
const file = ref(null)
const fileInput = ref(null)

const expandedLog = ref(null)
const loadingReplies = ref(false)
const replies = ref([])
const replyText = ref('')
const savingReply = ref(false)
const expandedReplies = ref({})

function onFileChange(e) {
    file.value = e.target.files[0] || null
}

function statusBadge(status) {
    const map = { pending: 'bg-secondary', in_progress: 'bg-primary', review: 'bg-info text-dark', completed: 'bg-success' }
    return map[status] || 'bg-secondary'
}

async function fetchData() {
    loading.value = true
    try {
        const [taskRes, logsRes] = await Promise.all([
            api.get('/tasks/' + route.params.taskId),
            api.get('/tasks/' + route.params.taskId + '/work-logs'),
        ])
        task.value = taskRes.data.data
        workLogs.value = logsRes.data.data || []

        const user = JSON.parse(localStorage.getItem('user') || '{}')
        const role = user.roles?.[0]?.name
        const assignedTo = task.value.assigned_to
        canLogWork.value = role === 'admin' || role === 'project-manager' || (role === 'employee' && assignedTo === user.id)
        canReply.value = role === 'admin' || role === 'project-manager'
    } catch (e) {
        console.error(e)
        flash('Failed to load data', 'danger')
    } finally {
        loading.value = false
    }
}

async function submitWorkLog() {
    saving.value = true
    errors.value = {}
    try {
        const fd = new FormData()
        fd.append('description', form.value.description)
        fd.append('hours_worked', form.value.hours_worked)
        if (form.value.logged_at) fd.append('logged_at', form.value.logged_at)
        if (file.value) fd.append('attachment', file.value)

        await api.post('/tasks/' + route.params.taskId + '/work-logs', fd, {
            headers: { 'Content-Type': 'multipart/form-data' },
        })
        flash('Work log created successfully')
        form.value = { description: '', hours_worked: '', logged_at: '' }
        file.value = null
        if (fileInput.value) fileInput.value.value = ''
        const logsRes = await api.get('/tasks/' + route.params.taskId + '/work-logs')
        workLogs.value = logsRes.data.data || []
    } catch (e) {
        if (e.response?.status === 422) {
            const errs = e.response.data.errors
            const flat = {}
            for (const key in errs) flat[key] = Array.isArray(errs[key]) ? errs[key][0] : errs[key]
            errors.value = flat
        } else {
            flash(e.response?.data?.message || 'Error logging work', 'danger')
        }
    } finally {
        saving.value = false
    }
}

async function toggleReplies(logId) {
    if (expandedReplies.value[logId]) {
        expandedReplies.value[logId] = false
        expandedLog.value = null
        return
    }
    expandedReplies.value = {}
    expandedReplies.value[logId] = true
    expandedLog.value = logId
    loadingReplies.value = true
    try {
        const res = await api.get('/work-logs/' + logId + '/replies')
        replies.value = res.data.data || []
    } catch (e) {
        console.error(e)
    } finally {
        loadingReplies.value = false
    }
}

async function submitReply() {
    const text = replyText.value
    if (!text?.trim()) return
    savingReply.value = true
    try {
        await api.post('/work-logs/' + expandedLog.value + '/replies', { reply: text })
        replyText.value = ''
        flash('Reply added')
        const res = await api.get('/work-logs/' + expandedLog.value + '/replies')
        replies.value = res.data.data || []
    } catch (e) {
        flash(e.response?.data?.message || 'Error adding reply', 'danger')
    } finally {
        savingReply.value = false
    }
}

onMounted(fetchData)
</script>
