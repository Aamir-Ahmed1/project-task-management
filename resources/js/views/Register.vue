<template>
    <div class="min-vh-100 d-flex align-items-center justify-content-center bg-light">
        <div class="card shadow" style="width: 400px;">
            <div class="card-body p-4">
                <h3 class="text-center mb-4">Task Manager</h3>
                <p class="text-center text-muted mb-4">Create a new account</p>
                <div v-if="error" class="alert alert-danger">{{ error }}</div>
                <div v-if="validationErrors.length" class="alert alert-danger">
                    <ul class="mb-0">
                        <li v-for="(msg, i) in validationErrors" :key="i">{{ msg }}</li>
                    </ul>
                </div>
                <form @submit.prevent="handleRegister">
                    <div class="mb-3">
                        <label class="form-label">Name</label>
                        <input type="text" v-model="name" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Email</label>
                        <input type="email" v-model="email" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Password</label>
                        <input type="password" v-model="password" class="form-control" required minlength="8">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Confirm Password</label>
                        <input type="password" v-model="passwordConfirmation" class="form-control" required>
                    </div>
                    <button type="submit" class="btn btn-primary w-100" :disabled="loading">
                        {{ loading ? 'Creating account...' : 'Register' }}
                    </button>
                </form>
                <p class="text-center mt-3">
                    Already have an account? <router-link to="/login">Sign in</router-link>
                </p>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import { useAuth } from '../composables/useAuth'

const { register } = useAuth()
const router = useRouter()
const name = ref('')
const email = ref('')
const password = ref('')
const passwordConfirmation = ref('')
const error = ref('')
const validationErrors = ref([])
const loading = ref(false)

async function handleRegister() {
    error.value = ''
    validationErrors.value = []
    loading.value = true
    try {
        await register(name.value, email.value, password.value, passwordConfirmation.value)
        router.push('/login')
    } catch (e) {
        const data = e.response?.data
        if (data?.errors) {
            const msgs = []
            for (const field in data.errors) {
                data.errors[field].forEach(m => msgs.push(m))
            }
            validationErrors.value = msgs
        } else {
            error.value = data?.message || 'Registration failed'
        }
    } finally {
        loading.value = false
    }
}
</script>
