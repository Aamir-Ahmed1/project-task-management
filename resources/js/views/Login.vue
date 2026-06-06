<template>
    <div class="min-vh-100 d-flex align-items-center justify-content-center bg-light">
        <div class="card shadow" style="width: 400px;">
            <div class="card-body p-4">
                <h3 class="text-center mb-4">Task Manager</h3>
                <p class="text-center text-muted mb-4">Sign in to your account</p>
                <div v-if="error" class="alert alert-danger">{{ error }}</div>
                <form @submit.prevent="handleLogin">
                    <div class="mb-3">
                        <label class="form-label">Email</label>
                        <input type="email" v-model="email" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Password</label>
                        <input type="password" v-model="password" class="form-control" required>
                    </div>
                    <button type="submit" class="btn btn-primary w-100" :disabled="loading">
                        {{ loading ? 'Signing in...' : 'Sign In' }}
                    </button>
                </form>
                <p class="text-center mt-3">
                    Don't have an account? <router-link to="/register">Register</router-link>
                </p>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import { useAuth } from '../composables/useAuth'

const { login, userRole } = useAuth()
const router = useRouter()
const email = ref('')
const password = ref('')
const error = ref('')
const loading = ref(false)

async function handleLogin() {
    error.value = ''
    loading.value = true
    try {
        await login(email.value, password.value)
        const role = userRole.value
        if (role === 'admin') router.push('/dashboard/admin')
        else if (role === 'project-manager') router.push('/dashboard/project-manager')
        else router.push('/dashboard/employee')
    } catch (e) {
        error.value = e.response?.data?.message || 'Invalid credentials'
    } finally {
        loading.value = false
    }
}
</script>
