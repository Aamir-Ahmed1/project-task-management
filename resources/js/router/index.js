import { createRouter, createWebHistory } from 'vue-router'
import { useAuth } from '../composables/useAuth'

const routes = [
    { path: '/login', name: 'login', component: () => import('../views/Login.vue'), meta: { guest: true } },
    { path: '/register', name: 'register', component: () => import('../views/Register.vue'), meta: { guest: true } },
    
    // Dashboard
    { path: '/dashboard/admin', name: 'dashboard.admin', component: () => import('../views/dashboard/Admin.vue'), meta: { role: 'admin' } },
    { path: '/dashboard/project-manager', name: 'dashboard.pm', component: () => import('../views/dashboard/ProjectManager.vue'), meta: { role: 'project-manager' } },
    { path: '/dashboard/employee', name: 'dashboard.employee', component: () => import('../views/dashboard/Employee.vue'), meta: { role: 'employee' } },
    
    // Projects
    { path: '/projects', name: 'projects.index', component: () => import('../views/projects/Index.vue'), meta: { auth: true } },
    { path: '/projects/create', name: 'projects.create', component: () => import('../views/projects/Create.vue'), meta: { auth: true } },
    { path: '/projects/:id', name: 'projects.show', component: () => import('../views/projects/Show.vue'), meta: { auth: true } },
    { path: '/projects/:id/edit', name: 'projects.edit', component: () => import('../views/projects/Edit.vue'), meta: { auth: true } },
    
    // Tasks
    { path: '/tasks', name: 'tasks.index', component: () => import('../views/tasks/Index.vue'), meta: { auth: true } },
    { path: '/tasks/create', name: 'tasks.create', component: () => import('../views/tasks/Create.vue'), meta: { auth: true } },
    { path: '/tasks/:id', name: 'tasks.show', component: () => import('../views/tasks/Show.vue'), meta: { auth: true } },
    { path: '/tasks/:id/edit', name: 'tasks.edit', component: () => import('../views/tasks/Edit.vue'), meta: { auth: true } },
    
    // Work Logs
    { path: '/tasks/:taskId/work-logs', name: 'worklogs.index', component: () => import('../views/worklogs/Index.vue'), meta: { auth: true } },
    
    // Notifications
    { path: '/notifications', name: 'notifications.index', component: () => import('../views/notifications/Index.vue'), meta: { auth: true } },
    
    // Reports
    { path: '/reports', name: 'reports.index', component: () => import('../views/reports/Index.vue'), meta: { role: ['admin', 'project-manager'] } },
    { path: '/reports/projects/:id', name: 'reports.project', component: () => import('../views/reports/Project.vue'), meta: { role: ['admin', 'project-manager'] } },
    { path: '/reports/employee/:id', name: 'reports.employee', component: () => import('../views/reports/Employee.vue'), meta: { role: ['admin', 'project-manager'] } },
    
    // Audit Logs
    { path: '/audit-logs', name: 'auditlogs.index', component: () => import('../views/auditlogs/Index.vue'), meta: { role: 'admin' } },
    
    // Default redirect
    { path: '/', redirect: '/login' },
    { path: '/:pathMatch(.*)*', redirect: '/login' },
]

const router = createRouter({
    history: createWebHistory(),
    routes,
})

router.beforeEach((to, from, next) => {
    const { isLoggedIn, userRole } = useAuth()
    
    if (to.meta.guest && isLoggedIn.value) {
        return next(getDashboardRoute(userRole.value))
    }
    
    if (to.meta.auth && !isLoggedIn.value) {
        return next('/login')
    }
    
    if (to.meta.role) {
        const allowedRoles = Array.isArray(to.meta.role) ? to.meta.role : [to.meta.role]
        if (!isLoggedIn.value) return next('/login')
        if (!allowedRoles.includes(userRole.value)) {
            return next(getDashboardRoute(userRole.value))
        }
    }
    
    next()
})

function getDashboardRoute(role) {
    switch(role) {
        case 'admin': return '/dashboard/admin'
        case 'project-manager': return '/dashboard/project-manager'
        case 'employee': return '/dashboard/employee'
        default: return '/login'
    }
}

export default router
