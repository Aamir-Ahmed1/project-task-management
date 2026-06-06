<template>
    <div class="d-flex" style="min-height: 100vh;">
        <Sidebar />
        <div class="flex-grow-1 d-flex flex-column">
            <Navbar />
            <main class="flex-grow-1 p-4 bg-light">
                <div v-if="flashMessage" :class="'alert alert-' + flashType + ' alert-dismissible fade show'" role="alert">
                    {{ flashMessage }}
                    <button type="button" class="btn-close" @click="flashMessage = null"></button>
                </div>
                <slot />
            </main>
        </div>
    </div>
</template>

<script setup>
import { ref, provide } from 'vue'
import Sidebar from './Sidebar.vue'
import Navbar from './Navbar.vue'

const flashMessage = ref(null)
const flashType = ref('success')

provide('flash', (message, type = 'success') => {
    flashMessage.value = message
    flashType.value = type
    setTimeout(() => flashMessage.value = null, 5000)
})
</script>
