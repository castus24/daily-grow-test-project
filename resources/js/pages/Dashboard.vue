<script setup>
import Sidebar from "../common/Sidebar.vue";
import {useAuthStore} from "../stores/auth.js";
import {ref} from 'vue';
import {useRouter} from "vue-router";

const authStore = useAuthStore()
const router = useRouter()

const items = [
    {title: 'Test 1'},
    {title: 'Test 2'},
    {title: 'Test 3'},
]

const drawer = ref(true);
const isMobile = ref(true);

const logout = () => {
    authStore.logout()
    router.push({name: 'login'})
}
</script>

<template>
    <v-app class="bg-blue-grey-lighten-5">
        <Sidebar v-model="drawer"/>

        <v-main>
            <v-container>
                <v-card color="#F6F8FA">
                    <v-layout>
                        <v-app-bar color="#F6F8FA">
                            <template v-slot:prepend>
                                <v-btn
                                    v-if="isMobile && !drawer"
                                    icon="mdi-menu"
                                    @click="drawer = true"
                                ></v-btn>
                                <v-btn
                                    icon="mdi-home"
                                    :to="{ name: 'home'}"
                                    class="mx-3"
                                >
                                </v-btn>
                                <v-menu
                                    transition="slide-x-transition"
                                >
                                    <template v-slot:activator="{ props }">
                                        <v-btn
                                            color="primary"
                                            variant="tonal"
                                            append-icon="mdi-arrow-down"
                                            v-bind="props"
                                        >
                                            Дома под ключ
                                        </v-btn>
                                    </template>

                                    <v-list>
                                        <v-list-item
                                            v-for="(item, i) in items"
                                            :key="i"
                                            :value="i"
                                        >
                                            <v-list-item-title>{{ item.title }}</v-list-item-title>
                                        </v-list-item>
                                    </v-list>
                                </v-menu>
                            </template>

                            <template v-slot:append class="d-flex">
                                <v-btn icon="mdi-bell"/>
                                <v-btn icon="mdi-cog" :to="{name: 'settings'}"/>
                                <v-btn icon="mdi-logout" @click="logout"/>
                            </template>
                        </v-app-bar>

                        <v-main>
                        </v-main>

                    </v-layout>
                </v-card>
            </v-container>
            <v-container>
                <router-view/>
            </v-container>
        </v-main>
    </v-app>
</template>

<style scoped>
</style>
