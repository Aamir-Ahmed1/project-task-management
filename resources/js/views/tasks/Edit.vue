<template>
    <AppLayout>
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h4 class="mb-0">Edit Task</h4>
            <router-link to="/tasks" class="btn btn-outline-secondary btn-sm">
                <i class="bi bi-arrow-left"></i> Back
            </router-link>
        </div>

        <div v-if="loading" class="text-center py-5"><div class="spinner-border"></div></div>

        <div v-else class="card shadow-sm">
            <div class="card-body">
                <form @submit.prevent="submit">
                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label">Name <span class="text-danger">*</span></label>
                            <input v-model="form.name" class="form-control" :class="{ 'is-invalid': errors.name }" required>
                            <div v-if="errors.name" class="invalid-feedback">{{ errors.name }}</div>
                        </div>

                        <div class="col-12">
                            <label class="form-label">Description</label>
                            <textarea v-model="form.description" class="form-control" rows="3" :class="{ 'is-invalid': errors.description }"></textarea>
                            <div v-if="errors.description" class="invalid-feedback">{{ errors.description }}</div>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label">Priority</label>
                            <select v-model="form.priority" class="form-select" :class="{ 'is-invalid': errors.priority }">
                                <option value="low">Low</option>
                                <option value="medium">Medium</option>
                                <option value="high">High</option>
                                <option value="critical">Critical</option>
                            </select>
                            <div v-if="errors.priority" class="invalid-feedback">{{ errors.priority }}</div>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label">Status</label>
                            <select v-model="form.status" class="form-select" :class="{ 'is-invalid': errors.status }">
                                <option value="to_do">To Do</option>
                                <option value="in_progress">In Progress</option>
                                <option value="in_review">In Review</option>
                                <option value="completed">Completed</option>
                                <option value="blocked">Blocked</option>
                            </select>
                            <div v-if="errors.status" class="invalid-feedback">{{ errors.status }}</div>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label">Deadline</label>
                            <input v-model="form.deadline" type="date" class="form-control" :class="{ 'is-invalid': errors.deadline }">
                            <div v-if="errors.deadline" class="invalid-feedback">{{ errors.deadline }}</div>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label">Estimated Hours</label>
                            <input v-model="form.estimated_hours" type="number" step="0.5" min="0" class="form-control" :class="{ 'is-invalid': errors.estimated_hours }">
                            <div v-if="errors.estimated_hours" class="invalid-feedback">{{ errors.estimated_hours }}</div>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label">Actual Hours</label>
                            <input v-model="form.actual_hours" type="number" step="0.5" min="0" class="form-control" :class="{ 'is-invalid': errors.actual_hours }">
                            <div v-if="errors.actual_hours" class="invalid-feedback">{{ errors.actual_hours }}</div>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label">Project <span class="text-danger">*</span></label>
                            <select v-model="form.project_id" class="form-select" :class="{ 'is-invalid': errors.project_id }" required>
                                <option value="">Select Project</option>
                                <option v-for="p in projects" :key="p.id" :value="p.id">{{ p.name }}</option>
                            </select>
                            <div v-if="errors.project_id" class="invalid-feedback">{{ errors.project_id }}</div>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label">Assign To (User ID)</label>
                            <input v-model="form.assigned_to" type="number" min="1" class="form-control" :class="{ 'is-invalid': errors.assigned_to }">
                            <div v-if="errors.assigned_to" class="invalid-feedback">{{ errors.assigned_to }}</div>
                            <div class="form-text">Enter the user ID of the assignee.</div>
                        </div>

                        <div class="col-12">
                            <button type="submit" class="btn btn-primary" :disabled="saving">
                                <span v-if="saving" class="spinner-border spinner-border-sm me-1"></span>
                                Update Task
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </AppLayout>
</template>

<script setup>
import { ref, onMounted, inject } from 'vue'
import { useRouter, useRoute } from 'vue-router'
import AppLayout from '../../components/AppLayout.vue'
import api from '../../composables/useApi'

const router = useRouter()
const route = useRoute()
const flash = inject('flash')
const loading = ref(true)
const saving = ref(false)
const errors = ref({})
const projects = ref([])

const form = ref({
    name: '',
    description: '',
    priority: 'medium',
    status: 'to_do',
    deadline: '',
    estimated_hours: '',
    actual_hours: '',
    project_id: '',
    assigned_to: '',
})

async function fetchData() {
    try {
        const [taskRes, projectsRes] = await Promise.all([
            api.get('/tasks/' + route.params.id),
            api.get('/projects', { params: { per_page: 100 } }),
        ])
        const task = taskRes.data.data
        projects.value = projectsRes.data.data
        form.value = {
            name: task.name || '',
            description: task.description || '',
            priority: task.priority || 'medium',
            status: task.status || 'to_do',
            deadline: task.deadline || '',
            estimated_hours: task.estimated_hours ?? '',
            actual_hours: task.actual_hours ?? '',
            project_id: task.project_id || '',
            assigned_to: task.assigned_to ?? '',
        }
    } catch (e) {
        flash('Failed to load task', 'danger')
        router.push('/tasks')
    } finally {
        loading.value = false
    }
}

async function submit() {
    saving.value = true
    errors.value = {}
    try {
        await api.put('/tasks/' + route.params.id, form.value)
        flash('Task updated successfully')
        router.push('/tasks')
    } catch (e) {
        if (e.response?.status === 422) {
            const errs = e.response.data.errors
            const flat = {}
            for (const key in errs) flat[key] = Array.isArray(errs[key]) ? errs[key][0] : errs[key]
            errors.value = flat
        } else {
            flash(e.response?.data?.message || 'Error updating task', 'danger')
        }
    } finally {
        saving.value = false
    }
}

onMounted(() => fetchData())
</script>
