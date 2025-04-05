<script setup>
import {reactive, ref} from 'vue'
import {useAuthStore} from '../../stores/auth.js'
import {useToast} from 'vue-toastification'

const authStore = useAuthStore()
const toast = useToast()

const isVisible = ref(false)
const loading = ref(false)

const form = reactive({
    email: null,
    password: null
})

const login = async () => {
    try {
        loading.value = true
        await authStore.login({
            email: form.email,
            password: form.password
        });

    } catch (error) {
        const errorMessage = error.response?.error || 'Account login error';

        toast.error(errorMessage)
    } finally {
        loading.value = false
    }
}
</script>

<template>
    <v-container class="mb-5">
        <v-card
            class="mx-auto pa-12 pb-8"
            elevation="8"
            max-width="448"
            rounded="lg"
        >
            <v-card-title class="d-flex justify-center mb-4">Вход в систему</v-card-title>

            <v-form>
                <v-text-field
                    v-model="form.email"
                    label="Email"
                    type="email"
                    density="comfortable"
                    required
                ></v-text-field>

                <v-text-field
                    v-model="form.password"
                    label="Пароль"
                    density="comfortable"
                    hint="Минимум 8 символов"
                    required
                    :type="isVisible ? 'text' : 'password'"
                    :append-icon="isVisible ? 'mdi-eye-off' : 'mdi-eye'"
                    @click:append="isVisible = !isVisible"
                    class="mb-5"
                ></v-text-field>

                <v-btn
                    class="mb-8"
                    color="blue-darken-3"
                    size="large"
                    variant="tonal"
                    :loading="loading"
                    :disabled="loading"
                    block
                    @click="login"
                >
                    Войти
                </v-btn>
            </v-form>

            <v-divider/>

            <v-card-text class="text-center">
                <v-btn
                    v-if="!authStore.isAuthenticated"
                    variant="plain"
                    class="text-blue"
                    :to="{ name: 'register' }"
                >
                    Регистрация
                    <v-icon icon="mdi-chevron-right"></v-icon>
                </v-btn>
            </v-card-text>
        </v-card>
    </v-container>
</template>
