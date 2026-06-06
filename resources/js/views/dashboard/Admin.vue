<template>
    <AppLayout>
        <h4 class="mb-4">Admin Dashboard</h4>
        <div v-if="loading" class="text-center py-5">
            <div class="spinner-border"></div>
        </div>
        <template v-else>
            <div class="row g-3 mb-4">
                <div class="col-md-3" v-for="stat in stats" :key="stat.label">
                    <div class="card border-0 shadow-sm" :style="'border-left: 4px solid ' + stat.color">
                        <div class="card-body">
                            <div class="text-muted small">{{ stat.label }}</div>
                            <div class="fs-3 fw-bold">{{ stat.value }}</div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="row g-3">
                <div class="col-md-6">
                    <div class="card shadow-sm">
                        <div class="card-header bg-white"><h6 class="mb-0">Tasks by Status</h6></div>
                        <div class="card-body">
                            <div v-for="(count, status) in dashboard.tasks_by_status" :key="status" class="mb-3">
                                <div class="d-flex justify-content-between small mb-1">
                                    <span>{{ status.replace('_', ' ') }}</span>
                                    <span>{{ count }}</span>
                                </div>
                                <div class="progress" style="height: 8px;">
                                    <div class="progress-bar" :style="'width: ' + (count / totalTasks * 100) + '%'"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="card shadow-sm">
                        <div class="card-header bg-white"><h6 class="mb-0">Recent Projects</h6></div>
                        <div class="card-body p-0">
                            <table class="table mb-0">
                                <thead><tr><th>Name</th><th>Status</th><th>Tasks</th><th>Progress</th></tr></thead>
                                <tbody>
                                    <tr v-for="p in dashboard.recent_projects" :key="p.id">
                                        <td><router-link :to="'/projects/' + p.id">{{ p.name }}</router-link></td>
                                        <td><span class="badge" :class="statusBadge(p.status)">{{ p.status }}</span></td>
                                        <td>{{ p.tasks_count }}</td>
                                        <td>
                                            <div class="progress" style="height: 6px;">
                                                <div class="progress-bar" :style="'width: ' + p.completion_percentage + '%'"></div>
                                            </div>
                                            <small>{{ p.completion_percentage }}%</small>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </template>
    </AppLayout>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import AppLayout from '../../components/AppLayout.vue'
import api from '../../composables/useApi'

const dashboard = ref({})
const loading = ref(true)

const stats = computed(() => [
    { label: 'Total Projects', value: dashboard.value.total_projects, color: '#0d6efd' },
    { label: 'Total Tasks', value: dashboard.value.total_tasks, color: '#198754' },
    { label: 'Active Employees', value: dashboard.value.active_employees, color: '#ffc107' },
    { label: 'Overdue Tasks', value: dashboard.value.overdue_tasks, color: '#dc3545' },
])

const totalTasks = computed(() => dashboard.value.total_tasks || 1)

function statusBadge(status) {
    const map = { planning: 'bg-secondary', active: 'bg-primary', completed: 'bg-success', archived: 'bg-dark' }
    return map[status] || 'bg-secondary'
}

onMounted(async () => {
    try {
        const res = await api.get('/dashboard/admin')
        dashboard.value = res.data.data
    } catch(e) { console.error(e) }
    finally { loading.value = false }
})
</script>
