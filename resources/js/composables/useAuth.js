import { ref, computed } from 'vue'
import api from './useApi'

const user = ref(null)
const token = ref(null)

export function useAuth() {
    const isLoggedIn = computed(() => !!token.value)
    const userRole = computed(() => user.value?.roles?.[0]?.name || null)
    
    async function login(email, password) {
        const res = await api.post('/login', { email, password })
        token.value = res.data.data.token
        user.value = res.data.data.user
        localStorage.setItem('token', token.value)
        localStorage.setItem('user', JSON.stringify(user.value))
        return res.data
    }
    
    async function logout() {
        await api.post('/logout')
        token.value = null
        user.value = null
        localStorage.removeItem('token')
        localStorage.removeItem('user')
    }
    
    function loadFromStorage() {
        const savedToken = localStorage.getItem('token')
        const savedUser = localStorage.getItem('user')
        if (savedToken && savedUser) {
            token.value = savedToken
            user.value = JSON.parse(savedUser)
        }
    }
    
    async function fetchUser() {
        const res = await api.get('/me')
        user.value = res.data.data
        return user.value
    }

    async function register(name, email, password, passwordConfirmation) {
        const res = await api.post('/register', { name, email, password, password_confirmation: passwordConfirmation })
        return res.data
    }

    return { user, token, isLoggedIn, userRole, login, logout, loadFromStorage, fetchUser, register }
}
