<script setup>
import {ref, computed, onMounted} from 'vue'
import {useToast} from 'vue-toastification'
import axios from "../plugins/axios.js"
import {useRouter} from "vue-router"

const toast = useToast()
const router = useRouter()

const loading = ref(false)
const saving = ref(false)
const valid = ref(false)
const form = ref(null)
const showClientSelector = ref(false) //todo check

const clientsCount = ref(0)
const selectedClients = ref([])
const availableClients = ref([])

const AUTO_TYPES = ['по расписанию']
const REGULAR_TYPES = ['ежедневно']

const currentMailing = ref({
    name: '',
    content: '',
    send_to_all: true,
    send_type: 'now',
    auto_type: 'по расписанию',
    regular_type: 'ежедневно',
    scheduled_at: null,
    send_time: '10:30',
    days_before_birthday: 7,
    status: 'draft',
    auto_sections: [],
    regular_sections: []
})

const totalRecipients = computed(() => {
    return currentMailing.value.send_to_all ? clientsCount.value : selectedClients.value.length
})

const canAddAutoSection = computed(() => {
    return currentMailing.value.auto_type !== 'по расписанию'
})

//todo
const showDaysInput = computed(() => {
    return currentMailing.value.auto_type === 'после' ||
        currentMailing.value.regular_type === 'ежедневно'
})

const loadClientsCount = async () => {
    try {
        clientsCount.value = availableClients.value.length
    } catch (error) {
        console.error('Error calculating clients count:', error)
        toast.error('Не удалось посчитать количество клиентов')
    }
}

const loadAvailableClients = async () => {
    loading.value = true
    try {
        const response = await axios.get('/api/clients')

        if (Array.isArray(response.data.data)) {
            availableClients.value = response.data.data.filter(client => client.email)
        } else {
            availableClients.value = []
            toast.error('Неверный формат данных от сервера')
        }
    } catch (error) {
        console.error('Error loading clients:', error)
        toast.error('Не удалось загрузить список клиентов')
        availableClients.value = []
    } finally {
        loading.value = false
    }
}

const saveMailing = async () => {
    const {valid} = await form.value.validate()
    if (!valid) return

    saving.value = true
    try {
        const mailingData = {
            ...currentMailing.value,
            client_ids: currentMailing.value.send_to_all ? [] : selectedClients.value.map(client => client.id),
            scheduled_at: currentMailing.value.send_type === 'auto' && currentMailing.value.scheduled_at && currentMailing.value.send_time
                ? `${currentMailing.value.scheduled_at} ${currentMailing.value.send_time}`
                : null,
            days_before_birthday: currentMailing.value.send_type === 'regular' ? 7 : null,
            send_time: currentMailing.value.send_type === 'regular' ? '03:20' : null,
            auto_sections: currentMailing.value.auto_sections.map(section => ({
                ...section,
                value: section.type === 'после' ? parseInt(section.value) : null
            })),
            regular_sections: currentMailing.value.regular_sections.map(section => ({
                ...section,
                days_before: 7
            }))
        }

        console.log('Sending mailing data:', mailingData) // Добавляем лог для отладки

        await axios.post('/api/mailing', mailingData)
        toast.success('Рассылка успешно создана')
        resetForm()
    } catch (error) {
        console.error('Error creating mailing:', error)
        if (error.response?.data?.errors) {
            const errorMessages = Object.values(error.response.data.errors).flat()
            toast.error('Ошибки валидации: ' + errorMessages.join(', '))
        } else {
            toast.error('Не удалось сохранить рассылку')
        }
    } finally {
        saving.value = false
    }
}

//todo
const updateMailing = async (id) => {
    const {valid} = await form.value.validate()
    if (!valid) return

    saving.value = true
    try {
        const mailingData = {
            ...currentMailing.value,
            client_ids: currentMailing.value.send_to_all ? [] : selectedClients.value.map(client => client.id),
            scheduled_at: currentMailing.value.send_type === 'auto' && currentMailing.value.scheduled_at && currentMailing.value.send_time
                ? `${currentMailing.value.scheduled_at} ${currentMailing.value.send_time}`
                : null,
            auto_sections: currentMailing.value.auto_sections.map(section => ({
                ...section,
                value: section.type === 'после' ? parseInt(section.value) : null
            })),
            regular_sections: currentMailing.value.regular_sections.map(section => ({
                ...section,
                days_before: parseInt(section.days_before)
            }))
        }

        await axios.put(`/api/mailing/${id}`, mailingData)
        toast.success('Рассылка успешно обновлена')
        resetForm()
    } catch (error) {
        console.error('Error updating mailing:', error)
        if (error.response?.data?.errors) {
            const errorMessages = Object.values(error.response.data.errors).flat()
            toast.error('Ошибки валидации: ' + errorMessages.join(', '))
        } else {
            toast.error('Не удалось обновить рассылку')
        }
    } finally {
        saving.value = false
    }
}

const resetForm = () => {
    if (form.value) {
        form.value.reset()
    }

    currentMailing.value = {
        name: '',
        content: '',
        send_to_all: true,
        send_type: 'now',
        auto_type: 'по расписанию',
        regular_type: 'ежедневно',
        scheduled_at: null,
        send_time: '10:30',
        days_before_birthday: 7,
        status: 'draft',
        auto_sections: [],
        regular_sections: []
    }
    selectedClients.value = []
}

//todo
const addAutoSection = () => {
    if (!canAddAutoSection.value) return

    currentMailing.value.auto_sections.push({
        type: currentMailing.value.auto_type,
        value: currentMailing.value.auto_type === 'после' ? 1 : null
    })
}

const addRegularSection = () => {
    currentMailing.value.regular_sections.push({
        type: currentMailing.value.regular_type,
        time: currentMailing.value.send_time,
        days_before: currentMailing.value.days_before_birthday
    })
}

const removeAutoSection = (index) => {
    currentMailing.value.auto_sections.splice(index, 1)
}

const removeRegularSection = (index) => {
    currentMailing.value.regular_sections.splice(index, 1)
}

onMounted(async () => {
    await loadAvailableClients()
    await loadClientsCount()
})
</script>

<template>
    <v-card>
        <v-card-title class="text-h5">
            Создать рассылку
            <div class="text-subtitle-2 mt-2">{{ currentMailing.name.length }}/150</div>
        </v-card-title>

        <v-card-text>
            <v-form ref="form" v-model="valid" @submit.prevent="saveMailing">
                <v-text-field
                    v-model="currentMailing.name"
                    label="Название рассылки"
                    :rules="[v => !!v || 'Название обязательно', v => v.length <= 150 || 'Максимум 150 символов']"
                    counter="150"
                    required
                ></v-text-field>

                <v-textarea
                    v-model="currentMailing.content"
                    label="Текст рассылки"
                    :rules="[v => !!v || 'Текст рассылки обязателен']"
                    required
                    auto-grow
                    rows="4"
                ></v-textarea>

                <div class="mb-6">
                    <div class="text-h6">Выберите получателей</div>
                    <p class="text-body-1 mb-4">
                        Выберите тех кому хотите отправить рассылку. Можете выбрать всех или определенные сегменты.
                        Управлять сегментами вы можете
                        <router-link to="/clients" class="text-primary">в списке клиентов</router-link>
                        .
                    </p>

                    <div class="text-subtitle-1 mb-4">
                        Рассылку получат {{ totalRecipients }} из {{ clientsCount }}
                    </div>

                    <v-radio-group v-model="currentMailing.send_to_all" class="mb-4">
                        <v-radio
                            :value="true"
                            label="Все клиенты"
                        ></v-radio>
                        <v-radio
                            :value="false"
                            label="Выбрать клиентов"
                        ></v-radio>
                    </v-radio-group>

                    <div v-if="!currentMailing.send_to_all">
                        <v-autocomplete
                            v-model="selectedClients"
                            :items="availableClients"
                            :loading="loading"
                            item-title="name"
                            item-value="id"
                            :rules="[v => v.length > 0 || 'Выберите хотя бы одного клиента']"
                            label="Выберите клиентов"
                            multiple
                            chips
                            closable-chips
                            return-object
                            :menu-props="{ closeOnContentClick: true }"
                        >
                            <template v-slot:chip="{ props, item }">
                                <v-chip
                                    v-bind="props"
                                    :text="item.raw.name"
                                ></v-chip>
                            </template>
                            <template v-slot:item="{ props, item }">
                                <v-list-item
                                    v-bind="props"
                                    :title="item.raw.name"
                                    :subtitle="item.raw.email"
                                ></v-list-item>
                            </template>
                        </v-autocomplete>
                    </div>
                </div>

                <div class="mb-6">
                    <div class="text-h6">Запустите рассылку</div>

                    <v-radio-group v-model="currentMailing.send_type" class="mb-4">
                        <v-radio
                            value="now"
                            label="Отправить сейчас"
                        ></v-radio>
                        <v-radio
                            value="auto"
                            label="Автоматическая отправка"
                        ></v-radio>
                        <v-radio
                            value="regular"
                            label="Регулярная рассылка"
                        ></v-radio>
                    </v-radio-group>

                    <div v-if="currentMailing.send_type === 'auto'" class="mb-4">
                        <v-radio-group v-model="currentMailing.auto_type" class="mb-4">
                            <v-radio
                                v-for="type in AUTO_TYPES"
                                :key="type"
                                :value="type"
                                :label="type"
                            ></v-radio>
                        </v-radio-group>

                        <div v-if="currentMailing.auto_type === 'по расписанию'" class="d-flex align-center">
                            <v-text-field
                                v-model="currentMailing.scheduled_at"
                                type="date"
                                label="Дата отправки"
                                :rules="[v => !!v || 'Выберите дату отправки']"
                                required
                                class="mr-4"
                            ></v-text-field>
                            <v-text-field
                                v-model="currentMailing.send_time"
                                type="time"
                                label="Время отправки"
                                :rules="[v => !!v || 'Выберите время отправки']"
                                required
                            ></v-text-field>
                        </div>
                    </div>

                    <div v-for="(section, index) in currentMailing.auto_sections"
                         :key="'auto-'+index"
                         class="ml-4 mt-2 d-flex align-center">
                        <v-select
                            v-model="section.type"
                            :items="AUTO_TYPES.filter(t => t !== 'по расписанию')"
                            variant="outlined"
                            density="compact"
                            hide-details
                            class="d-inline-block"
                            style="max-width: 200px;"
                        ></v-select>
                        <v-text-field
                            v-if="section.type === 'после'"
                            v-model="section.value"
                            type="number"
                            min="1"
                            :rules="[v => v > 0 || 'Значение должно быть больше 0']"
                            variant="outlined"
                            density="compact"
                            hide-details
                            class="d-inline-block ml-2"
                            style="max-width: 80px;"
                        ></v-text-field>
                        <v-btn
                            icon="mdi-delete"
                            size="small"
                            variant="text"
                            color="error"
                            class="ml-2"
                            @click="removeAutoSection(index)"
                        ></v-btn>
                    </div>

                    <div v-if="currentMailing.send_type === 'regular'" class="ml-4 d-flex align-center flex-wrap">
                        <v-select
                            v-model="currentMailing.regular_type"
                            :items="REGULAR_TYPES"
                            variant="outlined"
                            density="compact"
                            hide-details
                            class="d-inline-block mr-2"
                            style="max-width: 150px;"
                        ></v-select>
                        <span class="mx-2">в 10:30 за 7 дней до дня рождения</span>
                        <v-btn
                            icon="mdi-plus"
                            size="small"
                            variant="outlined"
                            class="ml-2"
                            @click="addRegularSection"
                        ></v-btn>
                    </div>

                    <div v-for="(section, index) in currentMailing.regular_sections"
                         :key="'regular-'+index"
                         class="ml-4 mt-2 d-flex align-center flex-wrap">
                        <v-select
                            v-model="section.type"
                            :items="REGULAR_TYPES"
                            variant="outlined"
                            density="compact"
                            hide-details
                            class="d-inline-block mr-2"
                            style="max-width: 150px;"
                        ></v-select>
                        <span class="mx-2">в 03:20 за 7 дней до дня рождения</span>
                        <v-btn
                            icon="mdi-delete"
                            size="small"
                            variant="text"
                            color="error"
                            class="ml-2"
                            @click="removeRegularSection(index)"
                        ></v-btn>
                    </div>
                </div>

                <div class="d-flex">
                    <v-btn
                        color="primary"
                        class="mr-4"
                        :loading="saving"
                        :disabled="!valid"
                        @click="saveMailing"
                    >
                        Сохранить
                    </v-btn>
                    <v-btn
                        variant="outlined"
                        color="grey"
                        @click="router.back()"
                    >
                        Назад
                    </v-btn>
                </div>
            </v-form>
        </v-card-text>
    </v-card>
</template>

<style scoped>
a {
    text-decoration: none;
}

:deep(.v-input--density-compact) {
    margin-top: 0;
    margin-bottom: 0;
}
</style>
