<template>
    <AppLayout>
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h4 class="mb-0">Edit Project</h4>
            <router-link to="/projects" class="btn btn-outline-secondary btn-sm">
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
                            <label class="form-label">Status</label>
                            <select v-model="form.status" class="form-select" :class="{ 'is-invalid': errors.status }">
                                <option value="planning">Planning</option>
                                <option value="active">Active</option>
                                <option value="completed">Completed</option>
                                <option value="archived">Archived</option>
                            </select>
                            <div v-if="errors.status" class="invalid-feedback">{{ errors.status }}</div>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label">Start Date</label>
                            <input v-model="form.start_date" type="date" class="form-control" :class="{ 'is-invalid': errors.start_date }">
                            <div v-if="errors.start_date" class="invalid-feedback">{{ errors.start_date }}</div>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label">End Date</label>
                            <input v-model="form.end_date" type="date" class="form-control" :class="{ 'is-invalid': errors.end_date }">
                            <div v-if="errors.end_date" class="invalid-feedback">{{ errors.end_date }}</div>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Project Manager ID</label>
                            <input v-model="form.project_manager_id" type="number" class="form-control" :class="{ 'is-invalid': errors.project_manager_id }">
                            <div v-if="errors.project_manager_id" class="invalid-feedback">{{ errors.project_manager_id }}</div>
                            <div class="form-text">Enter the user ID of the project manager.</div>
                        </div>

                        <div class="col-12">
                            <button type="submit" class="btn btn-primary" :disabled="saving">
                                <span v-if="saving" class="spinner-border spinner-border-sm me-1"></span>
                                Update Project
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

const form = ref({
    name: '',
    description: '',
    status: 'planning',
    start_date: '',
    end_date: '',
    project_manager_id: '',
})

onMounted(async () => {
    try {
        const res = await api.get('/projects/' + route.params.id)
        const project = res.data.data
        form.value = {
            name: project.name || '',
            description: project.description || '',
            status: project.status || 'planning',
            start_date: project.start_date || '',
            end_date: project.end_date || '',
            project_manager_id: project.project_manager_id || '',
        }
    } catch (e) {
        flash('Failed to load project', 'danger')
        router.push('/projects')
    } finally {
        loading.value = false
    }
})

async function submit() {
    saving.value = true
    errors.value = {}
    try {
        await api.put('/projects/' + route.params.id, form.value)
        flash('Project updated successfully')
        router.push('/projects')
    } catch (e) {
        if (e.response?.status === 422) {
            const errs = e.response.data.errors
            const flat = {}
            for (const key in errs) flat[key] = Array.isArray(errs[key]) ? errs[key][0] : errs[key]
            errors.value = flat
        } else {
            flash(e.response?.data?.message || 'Error updating project', 'danger')
        }
    } finally {
        saving.value = false
    }
}
</script>
