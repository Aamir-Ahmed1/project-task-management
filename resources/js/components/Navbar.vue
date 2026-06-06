<template>
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark px-3">
        <span class="navbar-brand">Task Manager</span>
        <div class="ms-auto d-flex align-items-center">
            <router-link to="/notifications" class="btn btn-outline-light btn-sm me-3 position-relative">
                <i class="bi bi-bell"></i>
                <span v-if="unreadCount > 0" class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">{{ unreadCount }}</span>
            </router-link>
            <div class="dropdown">
                <button class="btn btn-outline-light btn-sm dropdown-toggle" data-bs-toggle="dropdown">
                    <i class="bi bi-person-circle me-1"></i> {{ user?.name }}
                    <span class="badge bg-info ms-1">{{ userRole }}</span>
                </button>
                <ul class="dropdown-menu dropdown-menu-end">
                    <li><button class="dropdown-item" @click="logout">Logout</button></li>
                </ul>
            </div>
        </div>
    </nav>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useAuth } from '../composables/useAuth'
import api from '../composables/useApi'
import { useRouter } from 'vue-router'

const { user, logout: doLogout } = useAuth()
const router = useRouter()
const unreadCount = ref(0)

const userRole = user.value?.roles?.[0]?.name || ''

async function logout() {
    await doLogout()
    router.push('/login')
}

onMounted(async () => {
    try {
        const res = await api.get('/notifications?per_page=1&unread_only=true')
        unreadCount.value = res.data.meta?.total || 0
    } catch(e) {}
})
</script>
