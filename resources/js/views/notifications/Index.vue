<template>
    <AppLayout>
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h4 class="mb-0">Notifications</h4>
            <button v-if="hasUnread" class="btn btn-outline-primary btn-sm" @click="markAllRead" :disabled="markingAll">
                <span v-if="markingAll" class="spinner-border spinner-border-sm"></span>
                <span v-else><i class="bi bi-check-all"></i> Mark All as Read</span>
            </button>
        </div>

        <div v-if="loading" class="text-center py-5"><div class="spinner-border"></div></div>

        <div v-else-if="notifications.length === 0" class="text-center py-5 text-muted">
            <i class="bi bi-bell-slash" style="font-size: 3rem;"></i>
            <p class="mt-2">No notifications yet.</p>
        </div>

        <template v-else>
            <div class="list-group shadow-sm">
                <div
                    v-for="notif in notifications"
                    :key="notif.id"
                    class="list-group-item list-group-item-action d-flex align-items-start gap-3"
                    :class="{ 'border-primary border-2': !notif.read_at, 'bg-light': !notif.read_at }"
                    style="cursor: pointer;"
                    @click="markRead(notif)"
                >
                    <div class="mt-1">
                        <i :class="iconFor(notif.type)" style="font-size: 1.25rem;"></i>
                    </div>
                    <div class="flex-grow-1">
                        <div class="d-flex justify-content-between">
                            <strong v-if="!notif.read_at" class="small">{{ notif.data?.title || 'Notification' }}</strong>
                            <span v-else class="small">{{ notif.data?.title || 'Notification' }}</span>
                            <small class="text-muted ms-2 text-nowrap">{{ timeAgo(notif.created_at) }}</small>
                        </div>
                        <p class="mb-0 small text-muted">{{ notif.data?.message || '' }}</p>
                    </div>
                    <div v-if="!notif.read_at">
                        <span class="badge bg-primary rounded-pill">New</span>
                    </div>
                </div>
            </div>

            <nav v-if="meta.last_page > 1" class="mt-3">
                <ul class="pagination pagination-sm justify-content-center">
                    <li class="page-item" :class="{ disabled: meta.current_page === 1 }">
                        <button class="page-link" @click="fetchNotifications(meta.current_page - 1)">Previous</button>
                    </li>
                    <li v-for="page in meta.last_page" :key="page" class="page-item" :class="{ active: meta.current_page === page }">
                        <button class="page-link" @click="fetchNotifications(page)">{{ page }}</button>
                    </li>
                    <li class="page-item" :class="{ disabled: meta.current_page === meta.last_page }">
                        <button class="page-link" @click="fetchNotifications(meta.current_page + 1)">Next</button>
                    </li>
                </ul>
            </nav>
        </template>
    </AppLayout>
</template>

<script setup>
import { ref, computed, onMounted, inject } from 'vue'
import AppLayout from '../../components/AppLayout.vue'
import api from '../../composables/useApi'

const flash = inject('flash')
const notifications = ref([])
const meta = ref({ current_page: 1, last_page: 1 })
const loading = ref(true)
const markingAll = ref(false)

const hasUnread = computed(() => notifications.value.some(n => !n.read_at))

function timeAgo(dateStr) {
    if (!dateStr) return ''
    const now = new Date()
    const date = new Date(dateStr)
    const diff = Math.floor((now - date) / 1000)
    if (diff < 60) return 'just now'
    if (diff < 3600) return Math.floor(diff / 60) + 'm ago'
    if (diff < 86400) return Math.floor(diff / 3600) + 'h ago'
    if (diff < 2592000) return Math.floor(diff / 86400) + 'd ago'
    return date.toLocaleDateString()
}

function iconFor(type) {
    if (!type) return 'bi bi-bell text-secondary'
    if (type.includes('task')) return 'bi bi-check-circle text-primary'
    if (type.includes('project')) return 'bi bi-folder text-success'
    if (type.includes('comment') || type.includes('reply')) return 'bi bi-chat-dots text-info'
    if (type.includes('assign')) return 'bi bi-person-plus text-warning'
    return 'bi bi-bell text-secondary'
}

async function fetchNotifications(page = 1) {
    loading.value = true
    try {
        const res = await api.get('/notifications', { params: { page } })
        notifications.value = res.data.data
        meta.value = res.data.meta || { current_page: 1, last_page: 1 }
    } catch (e) {
        console.error(e)
        flash('Failed to load notifications', 'danger')
    } finally {
        loading.value = false
    }
}

async function markRead(notif) {
    if (notif.read_at) return
    try {
        await api.post('/notifications/' + notif.id + '/read')
        notif.read_at = new Date().toISOString()
    } catch (e) {
        console.error(e)
    }
}

async function markAllRead() {
    markingAll.value = true
    try {
        await api.post('/notifications/mark-all-read')
        fetchNotifications(meta.value.current_page)
        flash('All notifications marked as read')
    } catch (e) {
        flash(e.response?.data?.message || 'Error', 'danger')
    } finally {
        markingAll.value = false
    }
}

onMounted(() => fetchNotifications(1))
</script>
