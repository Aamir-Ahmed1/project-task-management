<template>
    <AppLayout>
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h4 class="mb-0">All Projects Report</h4>
        </div>

        <div v-if="loading" class="text-center py-5"><div class="spinner-border"></div></div>

        <template v-else>
            <div class="row g-3 mb-4">
                <div class="col-md-3">
                    <div class="card shadow-sm text-center">
                        <div class="card-body">
                            <h5 class="display-6 fw-bold text-primary">{{ report.total_projects }}</h5>
                            <small class="text-muted">Total Projects</small>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card shadow-sm text-center">
                        <div class="card-body">
                            <h5 class="display-6 fw-bold text-success">{{ report.active_projects }}</h5>
                            <small class="text-muted">Active / Planning</small>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card shadow-sm text-center">
                        <div class="card-body">
                            <h5 class="display-6 fw-bold text-dark">{{ report.archived_projects }}</h5>
                            <small class="text-muted">Archived</small>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card shadow-sm text-center">
                        <div class="card-body">
                            <h5 class="display-6 fw-bold text-warning">{{ report.total_tasks }}</h5>
                            <small class="text-muted">Total Tasks</small>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row g-3 mb-4">
                <div class="col-md-4">
                    <div class="card shadow-sm text-center">
                        <div class="card-body">
                            <h5 class="display-6 fw-bold text-info">{{ report.total_completed_tasks }}</h5>
                            <small class="text-muted">Completed Tasks</small>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card shadow-sm text-center">
                        <div class="card-body">
                            <h5 class="display-6 fw-bold text-danger">{{ report.total_overdue_tasks }}</h5>
                            <small class="text-muted">Overdue Tasks</small>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card shadow-sm text-center">
                        <div class="card-body">
                            <h5 class="display-6 fw-bold text-secondary">{{ report.average_completion_percentage }}%</h5>
                            <small class="text-muted">Avg Completion</small>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card shadow-sm">
                <div class="card-header bg-white"><h6 class="mb-0">Projects</h6></div>
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Project Name</th>
                                <th>Status</th>
                                <th>Tasks</th>
                                <th>Completion</th>
                                <th>Hours Logged</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="project in report.projects" :key="project.id">
                                <td>
                                    <router-link :to="'/reports/projects/' + project.id" class="fw-semibold">
                                        {{ project.name }}
                                    </router-link>
                                </td>
                                <td><span class="badge" :class="statusBadge(project.status)">{{ project.status }}</span></td>
                                <td>{{ project.tasks_count }}</td>
                                <td style="width: 180px;">
                                    <div class="d-flex align-items-center">
                                        <div class="progress flex-grow-1" style="height: 6px;">
                                            <div class="progress-bar" :style="'width: ' + (project.completion_percentage || 0) + '%'" :class="progressBg(project.completion_percentage)"></div>
                                        </div>
                                        <small class="ms-2">{{ project.completion_percentage || 0 }}%</small>
                                    </div>
                                </td>
                                <td>{{ project.total_hours_logged ?? '—' }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </template>
    </AppLayout>
</template>

<script setup>
import { ref, onMounted, inject } from 'vue'
import AppLayout from '../../components/AppLayout.vue'
import api from '../../composables/useApi'

const flash = inject('flash')
const loading = ref(true)
const report = ref({ projects: [] })

function statusBadge(status) {
    const map = { planning: 'bg-secondary', active: 'bg-primary', completed: 'bg-success', archived: 'bg-dark' }
    return map[status] || 'bg-secondary'
}

function progressBg(pct) {
    if (pct >= 100) return 'bg-success'
    if (pct >= 50) return 'bg-primary'
    return 'bg-warning'
}

onMounted(async () => {
    try {
        const res = await api.get('/reports/projects')
        report.value = res.data.data || { projects: [] }
    } catch (e) {
        console.error(e)
        flash('Failed to load report', 'danger')
    } finally {
        loading.value = false
    }
})
</script>
