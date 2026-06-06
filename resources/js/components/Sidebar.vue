<template>
    <div class="bg-dark text-white" style="width: 250px; min-height: 100vh;">
        <div class="p-3 text-center border-bottom border-secondary">
            <h5 class="mb-0">Task Manager</h5>
        </div>
        <div class="p-2">
            <router-link v-for="item in menuItems" :key="item.to" :to="item.to" 
                class="d-block p-2 text-white text-decoration-none rounded mb-1" 
                :class="{ 'bg-primary': isActive(item.to) }">
                <i :class="item.icon" class="me-2"></i> {{ item.label }}
            </router-link>
        </div>
    </div>
</template>

<script setup>
import { computed } from 'vue'
import { useAuth } from '../composables/useAuth'
import { useRoute } from 'vue-router'

const { userRole } = useAuth()
const route = useRoute()

const menus = {
    admin: [
        { label: 'Dashboard', to: '/dashboard/admin', icon: 'bi bi-speedometer2' },
        { label: 'Projects', to: '/projects', icon: 'bi bi-folder' },
        { label: 'Tasks', to: '/tasks', icon: 'bi bi-check-square' },
        { label: 'Reports', to: '/reports', icon: 'bi bi-graph-up' },
        { label: 'Audit Logs', to: '/audit-logs', icon: 'bi bi-journal-text' },
        { label: 'Notifications', to: '/notifications', icon: 'bi bi-bell' },
    ],
    'project-manager': [
        { label: 'Dashboard', to: '/dashboard/project-manager', icon: 'bi bi-speedometer2' },
        { label: 'Projects', to: '/projects', icon: 'bi bi-folder' },
        { label: 'Tasks', to: '/tasks', icon: 'bi bi-check-square' },
        { label: 'Reports', to: '/reports', icon: 'bi bi-graph-up' },
        { label: 'Notifications', to: '/notifications', icon: 'bi bi-bell' },
    ],
    employee: [
        { label: 'Dashboard', to: '/dashboard/employee', icon: 'bi bi-speedometer2' },
        { label: 'My Tasks', to: '/tasks', icon: 'bi bi-check-square' },
        { label: 'Notifications', to: '/notifications', icon: 'bi bi-bell' },
    ],
}

const menuItems = computed(() => menus[userRole.value] || [])
function isActive(to) { return route.path === to || route.path.startsWith(to + '/') }
</script>
