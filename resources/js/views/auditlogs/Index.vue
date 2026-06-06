<template>
    <AppLayout>
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h4 class="mb-0">Audit Logs</h4>
        </div>

        <div class="card shadow-sm mb-3">
            <div class="card-body">
                <div class="row g-2">
                    <div class="col-md-3">
                        <select v-model="filters.action" class="form-select form-select-sm" @change="fetchLogs(1)">
                            <option value="">All Actions</option>
                            <option value="created">Created</option>
                            <option value="updated">Updated</option>
                            <option value="deleted">Deleted</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <input v-model="filters.entity_type" class="form-control form-control-sm" placeholder="Entity Type (e.g. task)" @keyup.enter="fetchLogs(1)">
                    </div>
                    <div class="col-md-2">
                        <input v-model="filters.date_from" type="date" class="form-control form-control-sm" placeholder="From">
                    </div>
                    <div class="col-md-2">
                        <input v-model="filters.date_to" type="date" class="form-control form-control-sm" placeholder="To">
                    </div>
                    <div class="col-md-2 d-grid">
                        <button class="btn btn-outline-secondary btn-sm" @click="fetchLogs(1)">Filter</button>
                    </div>
                </div>
            </div>
        </div>

        <div v-if="loading" class="text-center py-5"><div class="spinner-border"></div></div>

        <div v-else-if="logs.length === 0" class="text-center py-5 text-muted">No audit logs found.</div>

        <template v-else>
            <div class="card shadow-sm">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Timestamp</th>
                                <th>User</th>
                                <th>Action</th>
                                <th>Entity</th>
                                <th>Details</th>
                                <th>IP Address</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="log in logs" :key="log.id">
                                <td><small>{{ log.created_at }}</small></td>
                                <td>{{ log.user?.name || 'System' }}</td>
                                <td><span class="badge" :class="actionBadge(log.action)">{{ log.action }}</span></td>
                                <td><small>{{ log.entity_type }} #{{ log.entity_id }}</small></td>
                                <td>
                                    <div v-if="log.previous_values || log.new_values" class="small">
                                        <template v-if="log.previous_values">
                                            <span class="text-danger">{{ truncateJson(log.previous_values) }}</span>
                                        </template>
                                        <template v-if="log.new_values">
                                            <span v-if="log.previous_values" class="mx-1">→</span>
                                            <span class="text-success">{{ truncateJson(log.new_values) }}</span>
                                        </template>
                                    </div>
                                    <span v-else class="text-muted small">—</span>
                                </td>
                                <td><small class="text-muted">{{ log.ip_address || '—' }}</small></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
            <nav v-if="meta.last_page > 1" class="mt-3">
                <ul class="pagination pagination-sm justify-content-center">
                    <li class="page-item" :class="{ disabled: meta.current_page === 1 }">
                        <button class="page-link" @click="fetchLogs(meta.current_page - 1)">Previous</button>
                    </li>
                    <li v-for="page in meta.last_page" :key="page" class="page-item" :class="{ active: meta.current_page === page }">
                        <button class="page-link" @click="fetchLogs(page)">{{ page }}</button>
                    </li>
                    <li class="page-item" :class="{ disabled: meta.current_page === meta.last_page }">
                        <button class="page-link" @click="fetchLogs(meta.current_page + 1)">Next</button>
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

const flash = inject('flash')
const logs = ref([])
const meta = ref({ current_page: 1, last_page: 1 })
const loading = ref(true)
const filters = ref({ action: '', entity_type: '', date_from: '', date_to: '' })

function actionBadge(action) {
    const map = { created: 'bg-success', updated: 'bg-primary', deleted: 'bg-danger' }
    return map[action] || 'bg-secondary'
}

function truncateJson(obj) {
    if (!obj) return ''
    const str = typeof obj === 'string' ? obj : JSON.stringify(obj)
    return str.length > 80 ? str.substring(0, 80) + '...' : str
}

async function fetchLogs(page = 1) {
    loading.value = true
    try {
        const params = {}
        for (const key in filters.value) {
            if (filters.value[key]) params[key] = filters.value[key]
        }
        params.page = page
        const res = await api.get('/audit-logs', { params })
        logs.value = res.data.data
        meta.value = res.data.meta || { current_page: 1, last_page: 1 }
    } catch (e) {
        console.error(e)
        flash('Failed to load audit logs', 'danger')
    } finally {
        loading.value = false
    }
}

onMounted(() => fetchLogs(1))
</script>
