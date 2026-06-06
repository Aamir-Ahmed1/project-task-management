<template>
    <AppLayout>
        <h4 class="mb-4">Project Manager Dashboard</h4>
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

            <div class="row g-3 mb-4">
                <div class="col-12">
                    <div class="card shadow-sm">
                        <div class="card-header bg-white"><h6 class="mb-0">Project Summaries</h6></div>
                        <div class="card-body p-0">
                            <table class="table mb-0">
                                <thead><tr><th>Name</th><th>Status</th><th>Tasks</th><th>Progress</th></tr></thead>
                                <tbody>
                                    <tr v-for="p in dashboard.managed_projects" :key="p.id">
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

            <div class="row g-3">
                <div class="col-md-6">
                    <div class="card shadow-sm">
                        <div class="card-header bg-white"><h6 class="mb-0">Upcoming Deadlines</h6></div>
                        <div class="card-body p-0">
                            <ul class="list-group list-group-flush">
                                <li v-for="task in dashboard.upcoming_deadlines" :key="task.id"
                                    class="list-group-item d-flex justify-content-between align-items-center">
                                    <div>
                                        <router-link :to="'/tasks/' + task.id" class="fw-semibold">{{ task.name }}</router-link>
                                        <div class="small text-muted">{{ task.project_name }}</div>
                                    </div>
                                    <span class="badge" :class="daysBadge(task.days_remaining ?? 0)">{{ task.days_remaining ?? 0 }} day{{ (task.days_remaining ?? 0) !== 1 ? 's' : '' }}</span>
                                </li>
                                <li v-if="!dashboard.upcoming_deadlines?.length" class="list-group-item text-muted text-center">No upcoming deadlines</li>
                            </ul>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="card shadow-sm">
                        <div class="card-header bg-white"><h6 class="mb-0">Employee Productivity</h6></div>
                        <div class="card-body p-0">
                            <table class="table mb-0">
                                <thead><tr><th>Name</th><th>Assigned</th><th>Completed</th></tr></thead>
                                <tbody>
                                    <tr v-for="emp in dashboard.employee_productivity" :key="emp.id">
                                        <td>{{ emp.name }}</td>
                                        <td>{{ emp.assigned_tasks }}</td>
                                        <td>{{ emp.completed_tasks }}</td>
                                    </tr>
                                    <tr v-if="!dashboard.employee_productivity?.length">
                                        <td colspan="3" class="text-muted text-center">No data</td>
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
    { label: 'Managed Projects', value: dashboard.value.total_managed_projects, color: '#0d6efd' },
    { label: 'Active Tasks', value: dashboard.value.active_tasks, color: '#198754' },
    { label: 'Completed Tasks', value: dashboard.value.completed_tasks, color: '#6f42c1' },
    { label: 'Overdue Tasks', value: dashboard.value.overdue_tasks_count, color: '#dc3545' },
])

function statusBadge(status) {
    const map = { planning: 'bg-secondary', active: 'bg-primary', completed: 'bg-success', archived: 'bg-dark' }
    return map[status] || 'bg-secondary'
}

function daysBadge(days) {
    if (days <= 0) return 'bg-danger'
    if (days <= 3) return 'bg-warning text-dark'
    if (days <= 7) return 'bg-info text-dark'
    return 'bg-success'
}

onMounted(async () => {
    try {
        const res = await api.get('/dashboard/project-manager')
        dashboard.value = res.data.data
    } catch(e) { console.error(e) }
    finally { loading.value = false }
})
</script>
