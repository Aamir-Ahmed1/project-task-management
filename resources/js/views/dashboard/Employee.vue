<template>
    <AppLayout>
        <h4 class="mb-4">Employee Dashboard</h4>
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
                <div class="col-md-6">
                    <div class="card shadow-sm">
                        <div class="card-header bg-white"><h6 class="mb-0">Tasks Due Soon</h6></div>
                        <div class="card-body p-0">
                            <ul class="list-group list-group-flush">
                                <li v-for="task in dashboard.tasks_due_soon" :key="task.id"
                                    class="list-group-item d-flex justify-content-between align-items-center">
                                    <div>
                                        <router-link :to="'/tasks/' + task.id" class="fw-semibold">{{ task.name }}</router-link>
                                        <div class="small text-muted">{{ task.project?.name }}</div>
                                    </div>
                                    <div class="text-end">
                                        <div class="small" :class="deadlineClass(task.days_remaining)">{{ task.days_remaining }} day{{ task.days_remaining !== 1 ? 's' : '' }}</div>
                                        <span class="badge" :class="priorityBadge(task.priority)">{{ task.priority }}</span>
                                    </div>
                                </li>
                                <li v-if="!dashboard.tasks_due_soon?.length" class="list-group-item text-muted text-center">No tasks due soon</li>
                            </ul>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="card shadow-sm">
                        <div class="card-header bg-white"><h6 class="mb-0">Recent Activity</h6></div>
                        <div class="card-body p-0">
                            <div class="list-group list-group-flush">
                                <div v-for="log in dashboard.recent_activity" :key="log.id"
                                    class="list-group-item">
                                    <div class="d-flex justify-content-between">
                                        <span class="fw-semibold">{{ log.task_name }}</span>
                                        <small class="text-muted">{{ log.logged_at }}</small>
                                    </div>
                                    <small class="text-muted" v-if="log.description">{{ log.description }}</small>
                                    <div class="small text-muted" v-if="log.hours_worked">
                                        Hours: {{ log.hours_worked }}
                                    </div>
                                </div>
                                <div v-if="!dashboard.recent_activity?.length" class="list-group-item text-muted text-center">No recent activity</div>
                            </div>
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
                                    <span class="text-capitalize">{{ status.replace('_', ' ') }}</span>
                                    <span>{{ count }}</span>
                                </div>
                                <div class="progress" style="height: 8px;">
                                    <div class="progress-bar" :style="'width: ' + (count / totalTasks * 100) + '%'"></div>
                                </div>
                            </div>
                            <div v-if="!totalTasks" class="text-muted small text-center">No tasks assigned</div>
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
    { label: 'Assigned Tasks', value: dashboard.value.total_assigned_tasks, color: '#0d6efd' },
    { label: 'Completed', value: dashboard.value.completed_tasks, color: '#198754' },
    { label: 'Pending', value: dashboard.value.pending_tasks, color: '#ffc107' },
    { label: 'Overdue', value: dashboard.value.overdue_tasks, color: '#dc3545' },
])

const totalTasks = computed(() => {
    const statuses = dashboard.value.tasks_by_status
    if (!statuses) return 0
    return Object.values(statuses).reduce((sum, c) => sum + c, 0) || 1
})

function deadlineClass(days) {
    if (days <= 0) return 'text-danger fw-bold'
    if (days <= 3) return 'text-warning fw-bold'
    if (days <= 7) return 'text-info'
    return 'text-muted'
}

function priorityBadge(priority) {
    const map = { low: 'bg-success', medium: 'bg-warning text-dark', high: 'bg-danger', urgent: 'bg-dark' }
    return map[priority] || 'bg-secondary'
}

onMounted(async () => {
    try {
        const res = await api.get('/dashboard/employee')
        dashboard.value = res.data.data
    } catch(e) { console.error(e) }
    finally { loading.value = false }
})
</script>
