<script setup>
import {ref, computed} from 'vue'
import {useToast} from 'vue-toastification'
import axios from "../plugins/axios.js";

const toast = useToast()

const loading = ref(false)
const settings = ref([])
const showDialog = ref(false)
const valid = ref(false)
const saving = ref(false)
const editing = ref(false)
const selectedProvider = ref(null)
const providerOptions = ref([])
const selectedPort = ref(null)
const itemsPerPage = ref(10)
const totalItems = ref(0)

const currentSetting = ref({
    mail_driver: 'smtp',
    mail_host: '',
    mail_port: 587,
    mail_username: '',
    mail_password: '',
    mail_encryption: 'tls',
    mail_from_address: '',
    mail_from_name: '',
    is_active: false
})

const headers = [
    {title: 'Провайдер', key: 'mail_host', value: item => `${item.mail_host}:${item.mail_port}`, sortable: false},
    {title: 'Пользователь', key: 'mail_username', sortable: true},
    {title: 'От', key: 'mail_from_address', sortable: true},
    {title: 'Активен', key: 'is_active', align: 'center', sortable: true},
    {title: 'Действия', key: 'actions', sortable: false, align: 'center'}
]

const loadSettings = async ({page, itemsPerPage, sortBy}) => {
    loading.value = true

    const sortParam = sortBy?.length
        ? sortBy.map((sort) => (sort.order === "desc" ? `-${sort.key}` : sort.key)).join(",")
        : "";

    const params = {
        page,
        itemsPerPage,
        sort: sortParam,
    };

    try {
        const response = await axios.get('/api/email-settings', { params })

        settings.value = response.data.settings;
        totalItems.value = response.data.settings.meta?.total || 0;
        providerOptions.value = response.data.defaultConfigs ? Object.values(response.data.defaultConfigs) : [];
    } catch (error) {
        toast.error('Не удалось загрузить настройки почты')
        console.error('Error loading settings:', error)
        console.error('Error response:', error.response?.data)
    } finally {
        loading.value = false
    }
}

const editItem = (item) => {
    currentSetting.value = {...item}
    editing.value = true
    showDialog.value = true

    const provider = providerOptions.value.find(p => p.mail_host === item.mail_host)

    selectedProvider.value = provider || {
        description: 'Пользовательский SMTP',
        mail_host: item.mail_host,
        mail_port: item.mail_port,
        mail_encryption: item.mail_encryption
    }

    if (provider) {
        selectedPort.value = {
            port: item.mail_port,
            encryption: item.mail_encryption
        }
    }
}

const applyProviderSettings = (provider) => {
    if (!provider) return

    currentSetting.value.mail_host = provider.mail_host
    currentSetting.value.mail_port = provider.mail_port
    currentSetting.value.mail_encryption = provider.mail_encryption
    selectedPort.value = {
        port: provider.mail_port,
        encryption: provider.mail_encryption
    }
}

const applyPortSettings = (port) => {
    if (!port) return
    currentSetting.value.mail_port = port.port
    currentSetting.value.mail_encryption = port.encryption
}

const saveSetting = async () => {
    if (!valid.value) return

    saving.value = true
    try {
        if (editing.value) {
            await axios.put(`/api/email-settings/${currentSetting.value.id}`, currentSetting.value)
            toast.success('Настройки успешно обновлены')
        } else {
            await axios.post('/api/email-settings', currentSetting.value)
            toast.success('Настройки успешно добавлены')
        }
        showDialog.value = false
        await loadSettings({page: 1, itemsPerPage: itemsPerPage.value})
    } catch (error) {
        if (error.response?.data?.errors) {
            toast.error('Ошибки валидации: ' + Object.values(error.response.data.errors).join(', '))
        } else {
            toast.error('Не удалось сохранить настройки')
        }
    } finally {
        saving.value = false
    }
}

const deleteItem = async (item) => {
    if (!confirm('Вы уверены, что хотите удалить эти настройки?')) return

    try {
        await axios.delete(`/api/email-settings/${item.id}`)
        toast.success('Настройки удалены')
        await loadSettings({page: 1, itemsPerPage: itemsPerPage.value})
    } catch (error) {
        toast.error('Не удалось удалить настройки')
    }
}

const testConnection = async (item) => {
    if (!confirm(`Тестовое письмо будет отправлено на адрес ${item.mail_from_address}. Продолжить?`)) return

    try {
        await axios.post(`/api/email-settings/${item.id}/test`)
        toast.success('Подключение успешно! Проверьте входящие письма на ' + item.mail_from_address)
    } catch (error) {
        toast.error(`Ошибка подключения: ${error.response?.data?.error || error.message}`)
    }
}

const activateSetting = async (item) => {
    try {
        await axios.post(`/api/email-settings/${item.id}/activate`)
        toast.success('Настройки успешно активированы')
        await loadSettings({page: 1, itemsPerPage: itemsPerPage.value})
    } catch (error) {
        toast.error('Не удалось активировать настройки')
    }
}

const resetForm = () => {
    currentSetting.value = {
        mail_driver: 'smtp',
        mail_host: '',
        mail_port: 587,
        mail_username: '',
        mail_password: '',
        mail_encryption: 'tls',
        mail_from_address: '',
        mail_from_name: '',
        is_active: false
    }
    selectedProvider.value = null
    selectedPort.value = null
    editing.value = false
}

const noDataMessage = computed(() => {
    if (!loading.value && settings.value.length === 0) {
        return 'Нет данных за выбранный период'
    }
    return ''
})
</script>

<template>
    <v-container>
        <v-card>
            <v-card-title>
                Настройки электронной почты
                <v-spacer></v-spacer>
                <v-btn class="ma-4" color="primary" @click="showDialog = true; resetForm()">
                    <v-icon start>mdi-plus</v-icon>
                    Добавить
                </v-btn>
            </v-card-title>

            <v-card-text>
                <v-data-table-server
                    v-model:items-per-page="itemsPerPage"
                    :headers="headers"
                    :items="settings"
                    :items-length="totalItems"
                    :loading="loading"
                    :no-data-text="noDataMessage"
                    item-value="id"
                    class="elevation-1 settings-table"
                    hover
                    @update:options="loadSettings"
                >
                    <template v-slot:item.is_active="{ item }">
                        <v-chip
                            :color="item.is_active ? 'success' : 'grey'"
                            size="small"
                        >
                            {{ item.is_active ? 'Активен' : 'Неактивен' }}
                        </v-chip>
                    </template>

                    <template v-slot:item.actions="{ item }">
                        <v-btn
                            size="small"
                            color="primary"
                            variant="text"
                            density="compact"
                            @click="editItem(item)"
                            class="me-2"
                        >
                            Изменить
                        </v-btn>
                        <v-btn
                            size="small"
                            color="success"
                            variant="text"
                            density="compact"
                            @click="testConnection(item)"
                            class="me-2"
                        >
                            Тест
                        </v-btn>
                        <v-btn
                            size="small"
                            color="warning"
                            variant="text"
                            density="compact"
                            @click="activateSetting(item)"
                            :disabled="item.is_active"
                            class="me-2"
                        >
                            Активировать
                        </v-btn>
                        <v-btn
                            size="small"
                            color="error"
                            variant="text"
                            density="compact"
                            @click="deleteItem(item)"
                        >
                            Удалить
                        </v-btn>
                    </template>
                </v-data-table-server>
            </v-card-text>
        </v-card>

        <v-dialog v-model="showDialog" max-width="800">
            <v-card>
                <v-card-title>{{ editing ? 'Редактировать настройки' : 'Добавить настройки' }}</v-card-title>
                <v-card-text>
                    <v-form ref="form" v-model="valid" @submit.prevent="saveSetting">
                        <v-row>
                            <v-col cols="12" md="6">
                                <v-select
                                    v-model="selectedProvider"
                                    :items="providerOptions"
                                    label="Почтовый сервис"
                                    item-title="description"
                                    return-object
                                    @update:model-value="applyProviderSettings"
                                ></v-select>
                            </v-col>

                            <v-col v-if="selectedProvider?.alternative_ports?.length" cols="12" md="6">
                                <v-select
                                    v-model="selectedPort"
                                    :items="selectedProvider.alternative_ports"
                                    label="Порт и шифрование"
                                    item-title="description"
                                    return-object
                                    @update:model-value="applyPortSettings"
                                ></v-select>
                            </v-col>

                            <v-col cols="12" md="6">
                                <v-text-field
                                    v-model="currentSetting.mail_host"
                                    label="SMTP сервер"
                                    required
                                    :disabled="!!selectedProvider"
                                ></v-text-field>
                            </v-col>

                            <v-col cols="12" md="6">
                                <v-text-field
                                    v-model="currentSetting.mail_port"
                                    label="Порт"
                                    type="number"
                                    required
                                    :disabled="!!selectedPort"
                                ></v-text-field>
                            </v-col>

                            <v-col cols="12" md="6">
                                <v-select
                                    v-model="currentSetting.mail_encryption"
                                    :items="['tls', 'ssl']"
                                    label="Шифрование"
                                    required
                                    :disabled="!!selectedPort"
                                ></v-select>
                            </v-col>

                            <v-col cols="12" md="6">
                                <v-text-field
                                    v-model="currentSetting.mail_username"
                                    label="Имя пользователя"
                                    required
                                ></v-text-field>
                            </v-col>

                            <v-col cols="12" md="6">
                                <v-text-field
                                    v-model="currentSetting.mail_password"
                                    label="Пароль"
                                    type="password"
                                    required
                                ></v-text-field>
                            </v-col>

                            <v-col cols="12" md="6">
                                <v-text-field
                                    v-model="currentSetting.mail_from_address"
                                    label="Адрес отправителя"
                                    type="email"
                                    required
                                ></v-text-field>
                            </v-col>

                            <v-col cols="12" md="6">
                                <v-text-field
                                    v-model="currentSetting.mail_from_name"
                                    label="Имя отправителя"
                                    required
                                ></v-text-field>
                            </v-col>

                            <v-col cols="12">
                                <v-checkbox
                                    v-model="currentSetting.is_active"
                                    label="Установить как активную конфигурацию"
                                    :disabled="currentSetting.is_active"
                                ></v-checkbox>
                            </v-col>
                        </v-row>

                        <v-card-actions>
                            <v-spacer></v-spacer>
                            <v-btn color="grey" @click="showDialog = false">Отмена</v-btn>
                            <v-btn color="primary" type="submit" :loading="saving">Сохранить</v-btn>
                        </v-card-actions>
                    </v-form>
                </v-card-text>
            </v-card>
        </v-dialog>
    </v-container>
</template>

<style scoped>
</style>
