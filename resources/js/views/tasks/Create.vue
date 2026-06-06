<template>
    <AppLayout>
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h4 class="mb-0">Create Task</h4>
            <router-link to="/tasks" class="btn btn-outline-secondary btn-sm">
                <i class="bi bi-arrow-left"></i> Back
            </router-link>
        </div>

        <div v-if="loadingProjects" class="text-center py-5"><div class="spinner-border"></div></div>

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
                                <option value="pending">Pending</option>
                                <option value="in_progress">In Progress</option>
                                <option value="review">Review</option>
                                <option value="completed">Completed</option>
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
                            <label class="form-label">Project <span class="text-danger">*</span></label>
                            <select v-model="form.project_id" class="form-select" :class="{ 'is-invalid': errors.project_id }" required>
                                <option value="">Select Project</option>
                                <option v-for="p in projects" :key="p.id" :value="p.id">{{ p.name }}</option>
                            </select>
                            <div v-if="errors.project_id" class="invalid-feedback">{{ errors.project_id }}</div>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label">Assign To</label>
                            <select v-model="form.assigned_to" class="form-select" :class="{ 'is-invalid': errors.assigned_to }">
                                <option value="">Select Employee</option>
                                <option v-for="u in employees" :key="u.id" :value="u.id">{{ u.name }} ({{ u.email }})</option>
                            </select>
                            <div v-if="errors.assigned_to" class="invalid-feedback">{{ errors.assigned_to }}</div>
                        </div>

                        <div class="col-12">
                            <button type="submit" class="btn btn-primary" :disabled="saving">
                                <span v-if="saving" class="spinner-border spinner-border-sm me-1"></span>
                                Create Task
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
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
const saving = ref(false)
const loadingProjects = ref(true)
const errors = ref({})
const projects = ref([])
const users = ref([])
const employees = computed(() => users.value.filter(u => u.role === 'employee'))

const form = ref({
    name: '',
    description: '',
    priority: 'medium',
    status: 'pending',
    deadline: '',
    estimated_hours: '',
    project_id: route.query.project_id || '',
    assigned_to: '',
})

async function fetchProjects() {
    try {
        const res = await api.get('/projects', { params: { per_page: 100 } })
        projects.value = res.data.data
    } catch (e) {
        console.error(e)
        flash('Failed to load projects', 'danger')
    } finally {
        loadingProjects.value = false
    }
}

async function submit() {
    saving.value = true
    errors.value = {}
    try {
        await api.post('/tasks', form.value)
        flash('Task created successfully')
        router.push('/tasks')
    } catch (e) {
        if (e.response?.status === 422) {
            const errs = e.response.data.errors
            const flat = {}
            for (const key in errs) flat[key] = Array.isArray(errs[key]) ? errs[key][0] : errs[key]
            errors.value = flat
        } else {
            flash(e.response?.data?.message || 'Error creating task', 'danger')
        }
    } finally {
        saving.value = false
    }
}

onMounted(async () => {
    fetchProjects()
    try {
        const res = await api.get('/users')
        users.value = res.data.data
    } catch (e) { console.error(e) }
})
</script>
