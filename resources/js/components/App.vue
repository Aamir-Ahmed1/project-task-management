<template>
    <div>
        <div v-if="flashMessage" class="position-fixed top-0 start-50 translate-middle-x mt-2" style="z-index: 9999;">
            <div :class="'alert alert-' + flashType + ' alert-dismissible fade show shadow'" role="alert">
                {{ flashMessage }}
                <button type="button" class="btn-close" @click="flashMessage = null"></button>
            </div>
        </div>
        <router-view />
    </div>
</template>

<script setup>
import { ref, provide } from 'vue'

const flashMessage = ref(null)
const flashType = ref('success')

provide('flash', (message, type = 'success') => {
    flashMessage.value = message
    flashType.value = type
    setTimeout(() => flashMessage.value = null, 5000)
})
</script>
