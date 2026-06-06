<template>
    <AppLayout>
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h4 class="mb-0">Projects</h4>
            <router-link v-if="canCreate" to="/projects/create" class="btn btn-primary btn-sm">
                <i class="bi bi-plus-lg"></i> Create Project
            </router-link>
        </div>

        <div class="card shadow-sm mb-3">
            <div class="card-body">
                <div class="row g-2">
                    <div class="col-md-3">
                        <select v-model="filters.status" class="form-select form-select-sm" @change="fetchProjects(1)">
                            <option value="">All Status</option>
                            <option value="planning">Planning</option>
                            <option value="active">Active</option>
                            <option value="completed">Completed</option>
                            <option value="archived">Archived</option>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <input v-model="filters.search" class="form-control form-control-sm" placeholder="Search projects..." @keyup.enter="fetchProjects(1)">
                    </div>
                    <div class="col-md-3">
                        <button class="btn btn-outline-secondary btn-sm w-100" @click="fetchProjects(1)">Filter</button>
                    </div>
                </div>
            </div>
        </div>

        <div v-if="loading" class="text-center py-5"><div class="spinner-border"></div></div>

        <div v-else-if="projects.length === 0" class="text-center py-5 text-muted">No projects found</div>

        <template v-else>
            <div class="card shadow-sm">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Name</th>
                                <th>Status</th>
                                <th>Project Manager</th>
                                <th>Start Date</th>
                                <th>End Date</th>
                                <th>Tasks</th>
                                <th>Progress</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="project in projects" :key="project.id">
                                <td><router-link :to="'/projects/' + project.id">{{ project.name }}</router-link></td>
                                <td><span class="badge" :class="statusBadge(project.status)">{{ project.status }}</span></td>
                                <td>{{ project.project_manager?.name || '—' }}</td>
                                <td>{{ project.start_date }}</td>
                                <td>{{ project.end_date }}</td>
                                <td>{{ project.tasks_count ?? 0 }}</td>
                                <td style="width: 150px;">
                                    <div class="d-flex align-items-center">
                                        <div class="progress flex-grow-1" style="height: 6px;">
                                            <div class="progress-bar" :style="'width: ' + (project.completion_percentage || 0) + '%'"></div>
                                        </div>
                                        <small class="ms-2">{{ project.completion_percentage || 0 }}%</small>
                                    </div>
                                </td>
                                <td>
                                    <div class="btn-group btn-group-sm">
                                        <router-link :to="'/projects/' + project.id" class="btn btn-outline-primary" title="View"><i class="bi bi-eye"></i></router-link>
                                        <router-link :to="'/projects/' + project.id + '/edit'" class="btn btn-outline-secondary" title="Edit"><i class="bi bi-pencil"></i></router-link>
                                        <button v-if="canArchive && project.status !== 'archived'" class="btn btn-outline-warning" title="Archive" @click="archiveProject(project.id)"><i class="bi bi-archive"></i></button>
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
                        <button class="page-link" @click="fetchProjects(meta.current_page - 1)">Previous</button>
                    </li>
                    <li v-for="page in meta.last_page" :key="page" class="page-item" :class="{ active: meta.current_page === page }">
                        <button class="page-link" @click="fetchProjects(page)">{{ page }}</button>
                    </li>
                    <li class="page-item" :class="{ disabled: meta.current_page === meta.last_page }">
                        <button class="page-link" @click="fetchProjects(meta.current_page + 1)">Next</button>
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

const projects = ref([])
const meta = ref({ current_page: 1, last_page: 1 })
const loading = ref(true)
const filters = ref({ status: '', search: '' })
const canCreate = ref(false)
const canArchive = ref(false)
const flash = inject('flash')

async function fetchProjects(page = 1) {
    loading.value = true
    try {
        const params = { ...filters.value, page }
        const res = await api.get('/projects', { params })
        projects.value = res.data.data
        meta.value = res.data.meta || { current_page: 1, last_page: 1 }
        const user = JSON.parse(localStorage.getItem('user') || '{}')
        const role = user.roles?.[0]
        canCreate.value = role === 'admin'
        canArchive.value = role === 'admin' || role === 'project-manager'
    } catch (e) {
        console.error(e)
    } finally {
        loading.value = false
    }
}

async function archiveProject(id) {
    if (!confirm('Archive this project?')) return
    try {
        await api.post('/projects/' + id + '/archive')
        flash('Project archived successfully')
        fetchProjects(meta.value.current_page)
    } catch (e) {
        flash(e.response?.data?.message || 'Error archiving project', 'danger')
    }
}

function statusBadge(status) {
    const map = { planning: 'bg-secondary', active: 'bg-primary', completed: 'bg-success', archived: 'bg-dark' }
    return map[status] || 'bg-secondary'
}

onMounted(() => fetchProjects(1))
</script>
