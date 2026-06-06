<template>
    <AppLayout>
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h4 class="mb-0">Project Report</h4>
            <router-link to="/reports" class="btn btn-outline-secondary btn-sm">
                <i class="bi bi-arrow-left"></i> Back
            </router-link>
        </div>

        <div v-if="loading" class="text-center py-5"><div class="spinner-border"></div></div>

        <template v-else>
            <div class="card shadow-sm mb-4">
                <div class="card-body">
                    <h5 class="mb-1">{{ report.project_name }}</h5>
                    <span class="badge" :class="statusBadge(report.project_status)">{{ report.project_status }}</span>
                    <span class="text-muted ms-2">ID: {{ report.project_id }}</span>
                </div>
            </div>

            <div class="row g-3 mb-4">
                <div class="col-md-3">
                    <div class="card shadow-sm text-center">
                        <div class="card-body">
                            <h5 class="display-6 fw-bold text-primary">{{ report.total_tasks }}</h5>
                            <small class="text-muted">Total Tasks</small>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card shadow-sm text-center">
                        <div class="card-body">
                            <h5 class="display-6 fw-bold text-success">{{ report.completed_tasks }}</h5>
                            <small class="text-muted">Completed</small>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card shadow-sm text-center">
                        <div class="card-body">
                            <h5 class="display-6 fw-bold text-warning">{{ report.pending_tasks }}</h5>
                            <small class="text-muted">Pending</small>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card shadow-sm text-center">
                        <div class="card-body">
                            <h5 class="display-6 fw-bold text-info">{{ report.total_hours_logged }}</h5>
                            <small class="text-muted">Hours Logged</small>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card shadow-sm">
                <div class="card-header bg-white"><h6 class="mb-0">Completion</h6></div>
                <div class="card-body">
                    <div class="d-flex align-items-center mb-3">
                        <div class="progress flex-grow-1" style="height: 20px;">
                            <div class="progress-bar" :class="progressBg(report.completion_percentage)" :style="'width: ' + (report.completion_percentage || 0) + '%'">
                                {{ report.completion_percentage || 0 }}%
                            </div>
                        </div>
                    </div>
                    <div v-if="tasksByStatus.length > 0">
                        <div v-for="item in tasksByStatus" :key="item.status" class="d-flex justify-content-between align-items-center mb-2">
                            <span class="text-capitalize small">{{ item.status.replace('_', ' ') }}</span>
                            <div class="d-flex align-items-center" style="width: 60%;">
                                <div class="progress flex-grow-1" style="height: 8px;">
                                    <div class="progress-bar" :class="statusBarBg(item.status)" :style="'width: ' + item.percent + '%'"></div>
                                </div>
                                <small class="ms-2 fw-semibold" style="width: 30px;">{{ item.count }}</small>
                            </div>
                        </div>
                    </div>
                    <div v-else class="text-muted small">No task data available.</div>
                </div>
            </div>
        </template>
    </AppLayout>
</template>

<script setup>
import { ref, computed, onMounted, inject } from 'vue'
import { useRoute } from 'vue-router'
import AppLayout from '../../components/AppLayout.vue'
import api from '../../composables/useApi'

const route = useRoute()
const flash = inject('flash')
const loading = ref(true)
const report = ref({})

const tasksByStatus = computed(() => {
    const statuses = report.value.tasks_by_status || {}
    const total = report.value.total_tasks || 1
    return Object.entries(statuses).map(([status, count]) => ({
        status,
        count,
        percent: Math.round((count / total) * 100),
    }))
})

function statusBadge(status) {
    const map = { planning: 'bg-secondary', active: 'bg-primary', completed: 'bg-success', archived: 'bg-dark' }
    return map[status] || 'bg-secondary'
}

function progressBg(pct) {
    if (pct >= 100) return 'bg-success'
    if (pct >= 50) return 'bg-primary'
    return 'bg-warning'
}

function statusBarBg(status) {
    const map = { pending: 'bg-secondary', in_progress: 'bg-primary', review: 'bg-info', completed: 'bg-success' }
    return map[status] || 'bg-secondary'
}

onMounted(async () => {
    try {
        const res = await api.get('/reports/projects/' + route.params.id)
        report.value = res.data.data || {}
    } catch (e) {
        console.error(e)
        flash('Failed to load project report', 'danger')
    } finally {
        loading.value = false
    }
})
</script>
