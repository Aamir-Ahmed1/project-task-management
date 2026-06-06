<template>
    <div class="d-flex" style="min-height: 100vh;">
        <Sidebar />
        <div class="flex-grow-1 d-flex flex-column">
            <Navbar />
            <main class="flex-grow-1 p-4 bg-light">
                <slot />
            </main>
        </div>
    </div>
</template>

<script setup>
import Sidebar from './Sidebar.vue'
import Navbar from './Navbar.vue'
</script>
