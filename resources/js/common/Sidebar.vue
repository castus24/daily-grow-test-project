<script setup>
import {computed, onMounted, onBeforeUnmount, ref} from 'vue';
import {useAuthStore} from "../stores/auth.js";

const authStore = useAuthStore()

const props = defineProps({
    modelValue: Boolean
});

const emit = defineEmits(['update:modelValue']);

const drawer = computed({
    get: () => props.modelValue,
    set: (value) => emit('update:modelValue', value)
});

const isMobile = ref(false);

function checkScreenSize() {
    isMobile.value = window.innerWidth < 960;
    drawer.value = !isMobile.value;
}

onMounted(() => {
    checkScreenSize();
    window.addEventListener('resize', checkScreenSize);
});

onBeforeUnmount(() => {
    window.removeEventListener('resize', checkScreenSize);
});


const user = computed(() => authStore.userData)
</script>

<template>
    <v-navigation-drawer
        v-model="drawer"
        :permanent="!isMobile"
        :temporary="isMobile"
        width="280"
    >
        <v-list>
            <v-list-item
                v-if="user"
                prepend-icon="mdi-account-circle"
                :subtitle="user.email"
                :title="user.name"
                class="mx-auto mb-4"
            />

            <v-divider/>

            <v-list-group value="Menu mailings" prepend-icon="mdi-wrench">
                <template v-slot:activator="{ props }">
                    <v-list-item v-bind="props" title="Меню рассылок"></v-list-item>
                </template>

                <v-list-item title="Аналитика" value="analytics" :to="{ name: 'analytics' }"/>
                <v-list-item title="Клиенты" value="clients" :to="{ name: 'clients' }"/>
                <v-list-item title="Рассылки" value="mailing" :to="{ name: 'mailing' }"/>
            </v-list-group>

            <v-divider/>

            <v-list-group value="Another" prepend-icon="mdi-cog-outline">
                <template v-slot:activator="{ props }">
                    <v-list-item v-bind="props" title="Прочее"></v-list-item>
                </template>

                <v-list-item title="Настройки" value="settings" :to="{ name: 'settings' }"/>
            </v-list-group>
        </v-list>
    </v-navigation-drawer>
</template>

<style scoped>
</style>
