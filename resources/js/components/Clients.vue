<script setup>
import {reactive, ref, computed} from 'vue'
import axios from "../plugins/axios.js"
import {useToast} from "vue-toastification"

const toast = useToast()

const loading = ref(true)
const totalItems = ref(0)
const tab = ref(false)
const itemsPerPage = ref(10)

const clients = ref([])
const search = ref('')
const dialog = ref(false)
const importDialog = ref(false)
const excelFile = ref(null)

const form = reactive({
    name: null,
    phone: null,
    email: null,
    birth_date: null
})


const headers = ref([
    {title: 'Название', key: 'name', align: 'start'},
    {title: 'Телефон', key: 'phone', align: 'start', sortable: false},
    {title: 'Email', key: 'email', align: 'start'},
    {title: 'Дата рождения', key: 'birth_date', align: 'start'},
    {title: 'Изменить', key: 'edit', align: 'center', sortable: false},
    {title: 'Удалить', key: 'delete', align: 'center', sortable: false},
])

const noDataMessage = computed(() => {
    if (!loading.value && clients.value.length === 0) {
        return 'Нет данных'
    }
    return ''
})

//todo add search filter
const loadClients = async ({page, itemsPerPage, sortBy}) => {
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
        const response = await axios.get("/api/clients", {params})
        const {data, meta} = response.data
        clients.value = data
        totalItems.value = meta.total
    } catch (error) {
        console.error("Error loading clients:", error)
        toast.error(error.response?.data.message || 'Ошибка загрузки данных о клиентах')
    } finally {
        loading.value = false
    }
};

const createClient = async () => {
    try {
        loading.value = true

        await axios.post("/api/clients", form)
        toast.success("Клиент успешно добавлен")
        dialog.value = false


        resetForm()
        await loadClients({page: 1, itemsPerPage: itemsPerPage.value})
    } catch (error) {
        console.error("Error creating client:", error)
        toast.error(error.response?.data.message || 'Ошибка при создании клиента')
    } finally {
        loading.value = false
    }
}

const importFromExcel = async () => {
    if (!excelFile.value) {
        toast.warning("Выберите файл для импорта")
        return
    }

    const formData = new FormData()
    formData.append('file', excelFile.value)

    try {
        loading.value = true
        const {data} = await axios.post("/api/clients/import", formData, {
            headers: {
                'Content-Type': 'multipart/form-data'
            }
        })

        toast.success(data.message)
        console.log('Import info: ', `Created: ${data.stats.created}, Updated: ${data.stats.updated}, Skipped: ${data.stats.skipped}`);

        importDialog.value = false
        excelFile.value = null

        await loadClients({page: 1, itemsPerPage: itemsPerPage.value})
    } catch (error) {
        console.error("Error importing clients:", error)
        toast.error(error.response?.data.message || 'Ошибка импорта данных')
    } finally {
        loading.value = false
    }
}

const resetForm = () => {
    form.value = {
        name: null,
        phone: null,
        email: null,
        birth_date: null
    }
}

const handleFileUpload = (event) => {
    excelFile.value = event.target.files[0]
}

const editDialog = ref(false)
const editingClient = ref(null)

const openEditDialog = (client) => {
    editingClient.value = client
    editDialog.value = true
}

const updateClient = async () => {
    try {
        const {data} = await axios.put(`/api/clients/${editingClient.value.id}`, editingClient.value)

        editDialog.value = false
        await loadClients({page: 1, itemsPerPage: itemsPerPage.value})

        toast.success(data.message)
    } catch (error) {
        console.error("Error editing client:", error)
        const errorMessage = error.response?.error || 'Ошибка при изменении данных о клиенте';
        toast.error(errorMessage)
    }
}

const deleteClient = async (id) => {
    try {
        const {data} = await axios.delete(`/api/clients/${id}`)

        await loadClients({page: 1, itemsPerPage: itemsPerPage.value})
        toast.success(data.message)
    } catch (error) {
        console.error("Error deleting client:", error)
        const errorMessage = error.response?.error || 'Ошибка при удалении клиента';
        toast.error(errorMessage)
    }
}
</script>

<template>
    <v-card class="clients-card" elevation="2">
        <v-tabs
            v-model="tab"
            bg-color="primary"
            bg-opacity="0.1"
            center-active
            class="mb-2"
        >
            <v-tab value="clients">
                <v-icon start>mdi-account-group</v-icon>
                Список клиентов
            </v-tab>
            <v-tab value="segments">
                <v-icon start>mdi-chart-pie</v-icon>
                Сегменты
            </v-tab>
            <v-tab value="purchases">
                <v-icon start>mdi-cart</v-icon>
                Покупки
            </v-tab>
        </v-tabs>

        <v-card-text>
            <v-row align="center" class="mb-4">
                <v-col cols="12" sm="6" md="4">
                    <v-text-field
                        v-model="search"
                        placeholder="Поиск и фильтр"
                        prepend-inner-icon="mdi-magnify"
                        variant="outlined"
                        density="compact"
                        hide-details
                        class="search-field"
                    ></v-text-field>
                </v-col>
                <v-spacer></v-spacer>
                <v-col cols="auto" class="d-flex justify-space-between align-center">
                    <div class="text-subtitle-1 mr-3 clients-counter">
                        <v-icon start color="primary" class="mr-1">mdi-account-multiple</v-icon>
                        {{ totalItems }}
                    </div>
                    <v-form class="d-flex gap-2">
                        <v-btn
                            color="primary"
                            size="large"
                            prepend-icon="mdi-plus"
                            variant="tonal"
                            :disabled="loading"
                            :loading="loading"
                            @click="dialog = true"
                        >
                            Добавить
                        </v-btn>
                        <v-btn
                            color="success"
                            size="large"
                            prepend-icon="mdi-file-excel"
                            variant="tonal"
                            :disabled="loading"
                            :loading="loading"
                            @click="importDialog = true"
                            class="ml-2"
                        >
                            Импорт из Excel
                        </v-btn>
                    </v-form>
                </v-col>
            </v-row>
            <v-data-table-server
                v-model:items-per-page="itemsPerPage"
                :headers="headers"
                :items="clients"
                :items-length="totalItems"
                :loading="loading"
                :no-data-text="noDataMessage"
                class="elevation-1 clients-table"
                hover
                @update:options="loadClients"
            >
                <template #item.edit="{ item }">
                    <v-btn
                        color="warning"
                        variant="tonal"
                        density="comfortable"
                        icon
                        @click="openEditDialog(item)"
                    >
                        <v-icon>mdi-pencil</v-icon>
                    </v-btn>
                </template>

                <template #item.delete="{ item }">
                    <v-btn
                        color="error"
                        variant="tonal"
                        density="comfortable"
                        icon
                        @click="deleteClient(item.id)"
                    >
                        <v-icon>mdi-delete</v-icon>
                    </v-btn>
                </template>
            </v-data-table-server>
        </v-card-text>

        <v-dialog v-model="editDialog" max-width="600">
            <v-card>
                <v-card-title class="text-h5 pa-4 bg-primary text-white">
                    <v-icon start class="mr-2">mdi-account-edit</v-icon>
                    Редактировать клиента
                </v-card-title>
                <v-card-text class="pa-4">
                    <v-form @submit.prevent="updateClient">
                        <v-text-field
                            v-model="editingClient.name"
                            label="Название"
                            variant="outlined"
                            density="comfortable"
                            class="mb-3"
                        />
                        <v-text-field
                            v-model="editingClient.phone"
                            label="Телефон"
                            variant="outlined"
                            density="comfortable"
                            class="mb-3"
                        />
                        <v-text-field
                            v-model="editingClient.email"
                            label="Email"
                            variant="outlined"
                            density="comfortable"
                            class="mb-3"
                        />
                        <v-text-field
                            v-model="editingClient.birth_date"
                            type="date"
                            label="Дата рождения"
                            variant="outlined"
                            density="comfortable"
                            class="mb-3"
                        />
                        <v-card-actions class="pa-0">
                            <v-spacer></v-spacer>
                            <v-btn variant="tonal" @click="editDialog = false">Отмена</v-btn>
                            <v-btn
                                type="submit"
                                color="primary"
                                :loading="loading"
                                variant="tonal"
                            >
                                Сохранить
                            </v-btn>
                        </v-card-actions>
                    </v-form>
                </v-card-text>
            </v-card>
        </v-dialog>

        <v-dialog v-model="dialog" max-width="600">
            <v-card>
                <v-card-title class="text-h5 pa-4 bg-primary text-white">
                    <v-icon start class="mr-2">mdi-account-plus</v-icon>
                    Добавить нового клиента
                </v-card-title>
                <v-card-text class="pa-4">
                    <v-form @submit.prevent="createClient">
                        <v-text-field
                            v-model="form.name"
                            label="Название"
                            required
                            variant="outlined"
                            density="comfortable"
                            class="mb-3"
                        ></v-text-field>
                        <v-text-field
                            v-model="form.phone"
                            label="Телефон"
                            required
                            variant="outlined"
                            density="comfortable"
                            class="mb-3"
                        ></v-text-field>
                        <v-text-field
                            v-model="form.email"
                            label="Email"
                            type="email"
                            variant="outlined"
                            density="comfortable"
                            class="mb-3"
                        ></v-text-field>
                        <v-text-field
                            v-model="form.birth_date"
                            label="Дата рождения"
                            type="date"
                            variant="outlined"
                            density="comfortable"
                            class="mb-3"
                        ></v-text-field>
                        <v-card-actions class="pa-0">
                            <v-spacer></v-spacer>
                            <v-btn variant="tonal" @click="dialog = false">Отмена</v-btn>
                            <v-btn
                                color="primary"
                                variant="tonal"
                                type="submit"
                                :loading="loading"
                            >
                                Сохранить
                            </v-btn>
                        </v-card-actions>
                    </v-form>
                </v-card-text>
            </v-card>
        </v-dialog>

        <v-dialog v-model="importDialog" max-width="600">
            <v-card>
                <v-card-title class="text-h5 pa-4 bg-primary text-white">
                    <v-icon start class="mr-2">mdi-file-excel</v-icon>
                    Импорт клиентов из Excel
                </v-card-title>
                <v-card-text class="pa-4">
                    <v-file-input
                        accept=".xlsx,.xls,.csv"
                        label="Выберите Excel файл"
                        prepend-icon="mdi-file-excel"
                        variant="outlined"
                        density="comfortable"
                        class="mb-2"
                        @change="handleFileUpload"
                    ></v-file-input>
                    <div class="text-caption text-grey mb-4">
                        <v-icon start size="small" class="mr-1">mdi-information</v-icon>
                        Поддерживаются файлы формата XLSX, XLS, CSV
                    </div>
                    <v-card-actions class="pa-0">
                        <v-spacer></v-spacer>
                        <v-btn variant="tonal" @click="importDialog = false">Отмена</v-btn>
                        <v-btn
                            color="success"
                            variant="tonal"
                            @click="importFromExcel"
                            :disabled="!excelFile"
                            :loading="loading"
                        >
                            Импортировать
                        </v-btn>
                    </v-card-actions>
                </v-card-text>
            </v-card>
        </v-dialog>
    </v-card>
</template>

<style scoped>
.clients-card {
    border-radius: 8px;
}

.clients-table {
    border-radius: 8px;
}

.search-field {
    border-radius: 8px;
}

.clients-counter {
    background-color: rgba(var(--v-theme-primary), 0.1);
    padding: 8px 16px;
    border-radius: 20px;
    font-weight: 500;
}

:deep(.v-btn) {
    text-transform: none;
    letter-spacing: normal;
}

:deep(.v-data-table) {
    border-radius: 8px;
}

:deep(.v-data-table-footer) {
    border-bottom-left-radius: 8px;
    border-bottom-right-radius: 8px;
}
</style>
