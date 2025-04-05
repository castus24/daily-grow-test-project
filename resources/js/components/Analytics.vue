<script setup>
import {ref, computed} from 'vue'
import axios from "../plugins/axios.js"
import {useToast} from 'vue-toastification'

const toast = useToast()
const loading = ref(false)
const mailings = ref([])
const totalItems = ref(0)
const showDatePicker = ref(false)
const itemsPerPage = ref(10)

const formatDate = (date) => {
    return date.toISOString().substring(0, 10)
}

const validateDateRange = (start, end) => {
    const startDate = new Date(start)
    const endDate = new Date(end)
    return startDate <= endDate
}

const periods = [
    {title: '7 дней', days: 7},
    {title: 'месяц', days: 30},
    {title: 'квартал', days: 90},
    {title: 'год', days: 365}
]

const dates = ref({
    start: formatDate(new Date(new Date().setDate(1))),
    end: formatDate(new Date())
})

const selectedPeriod = ref(null)

const totals = ref({
    sent: 0,
    delivered: 0
})

const noDataMessage = computed(() => {
    if (!loading.value && mailings.value.length === 0) {
        return 'Нет данных за выбранный период'
    }
    return ''
})

const loadAnalytics = async ({page, itemsPerPage, sortBy}) => {
    if (!validateDateRange(dates.value.start, dates.value.end)) {
        toast.error('Начальная дата не может быть позже конечной')
        return
    }

    loading.value = true

    const sortParam = sortBy?.length
        ? sortBy.map((sort) => (sort.order === "desc" ? `-${sort.key}` : sort.key)).join(",")
        : "";

    const params = {
        page,
        itemsPerPage,
        sort: sortParam,
        'filter[date_from]': dates.value.start,
        'filter[date_to]':  dates.value.end,
    };

    try {
        const response = await axios.get('/api/mailing/analytics', {params})
        const {data, meta} = response.data

        console.log('Данные от сервера:', data)
        mailings.value = data
        totalItems.value = meta.total
        calculateTotals()
    } catch (error) {
        console.error('Error loading analytics:', error)
        toast.error(error.response?.data?.message || 'Не удалось загрузить статистику')
        mailings.value = []
        totals.value = {sent: 0, delivered: 0}
    } finally {
        loading.value = false
    }
}

const calculateTotals = () => {
    totals.value = mailings.value.reduce((acc, mailing) => {
        acc.sent += mailing.sent_count || 0
        acc.delivered += mailing.delivered_count || 0
        return acc
    }, {sent: 0, delivered: 0})
}

const setQuickPeriod = (days) => {
    const end = new Date()
    const start = new Date()
    start.setDate(start.getDate() - days)

    dates.value = {
        start: formatDate(start),
        end: formatDate(end)
    }

    selectedPeriod.value = days
    loadAnalytics({page: 1, itemsPerPage: itemsPerPage.value})
}

const updateDateRange = async () => {
    if (!validateDateRange(dates.value.start, dates.value.end)) {
        toast.error('Начальная дата не может быть позже конечной')
        return
    }
    await loadAnalytics({page: 1, itemsPerPage: itemsPerPage.value})
}

const getStatusColor = (status) => {
    switch (status) {
        case 'scheduled':
        case 'pending':
            return 'warning'
        case 'sent':
        case 'delivered':
            return 'success'
        case 'draft':
            return 'grey'
        case 'failed':
            return 'error'
        default:
            return 'grey'
    }
}

const getStatusText = (status) => {
    switch (status) {
        case 'scheduled':
        case 'pending':
            return 'Запланировано'
        case 'sent':
        case 'delivered':
            return 'Отправлено'
        case 'draft':
            return 'Черновик'
        case 'failed':
            return 'Ошибка'
        default:
            return 'В обработке'
    }
}

const calculateDeliveryPercentage = (mailing) => {
    const sent = Math.max(0, mailing?.sent_count || 0)
    const delivered = Math.max(0, mailing?.delivered_count || 0)

    if (sent === 0) return 0
    if (delivered > sent) return 100 // максимум 100%
    return Math.round((delivered / sent) * 100)
}

const headers = [
    {title: 'ID', key: 'id', sortable: false, align: 'start'},
    {title: 'Название', key: 'name', align: 'start'},
    {title: 'Статус', key: 'status', align: 'start'},
    {title: 'Отправлено', key: 'sent_count', sortable: false, align: 'center'},
    {title: 'Доставлено', key: 'delivered_count', sortable: false, align: 'center'}
]
</script>

<template>
    <v-card>
        <v-card-text>
            <!-- Фильтры -->
            <div class="d-flex align-center mb-6">
                <v-menu
                    v-model="showDatePicker"
                    :close-on-content-click="false"
                >
                    <template v-slot:activator="{ props }">
                        <v-btn
                            variant="outlined"
                            v-bind="props"
                            class="mr-4"
                        >
                            {{ dates.start }} - {{ dates.end }}
                        </v-btn>
                    </template>

                    <v-card>
                        <v-card-text>
                            <v-row>
                                <v-col cols="12" sm="6">
                                    <v-text-field
                                        v-model="dates.start"
                                        label="Начальная дата"
                                        type="date"
                                        variant="outlined"
                                        density="compact"
                                    />
                                </v-col>
                                <v-col cols="12" sm="6">
                                    <v-text-field
                                        v-model="dates.end"
                                        label="Конечная дата"
                                        type="date"
                                        variant="outlined"
                                        density="compact"
                                    />
                                </v-col>
                            </v-row>
                        </v-card-text>
                        <v-card-actions>
                            <v-spacer/>
                            <v-btn
                                color="primary"
                                @click="updateDateRange(); showDatePicker = false"
                            >
                                Применить
                            </v-btn>
                        </v-card-actions>
                    </v-card>
                </v-menu>

                <div class="d-flex">
                    <v-btn
                        v-for="period in periods"
                        :key="period.days"
                        variant="outlined"
                        :color="selectedPeriod === period.days ? 'primary' : undefined"
                        class="mx-1"
                        @click="setQuickPeriod(period.days)"
                    >
                        {{ period.title }}
                    </v-btn>
                </div>
                <v-spacer/>
                <div class="text-subtitle-1 mr-3 clients-counter">
                    <v-icon start color="primary" class="mr-1">mdi-message-text</v-icon>
                    {{ totalItems }}
                </div>
            </div>

            <v-data-table-server
                v-model:items-per-page="itemsPerPage"
                :headers="headers"
                :items="mailings"
                :items-length="totalItems"
                :loading="loading"
                :no-data-text="noDataMessage"
                class="elevation-1 analytics-table"
                hover
                @update:options="loadAnalytics"
            >
                <template v-slot:item.status="{ item }">
                    <v-chip
                        :color="getStatusColor(item.status)"
                        size="small"
                        class="text-caption"
                    >
                        {{ getStatusText(item.status) }}
                    </v-chip>
                </template>

                <template v-slot:item.sent_count="{ item }">
                    <div class="d-flex align-center justify-center">
                        <span>{{ item.sent_count || 0 }}</span>
                    </div>
                </template>

                <template v-slot:item.delivered_count="{ item }">
                    <div class="d-flex align-center justify-center">
                        <span>{{ item.delivered_count || 0 }}</span>
                        <span class="text-caption text-grey ml-1">
                                {{ calculateDeliveryPercentage(item) }}%
                            </span>
                    </div>
                </template>
            </v-data-table-server>
        </v-card-text>
    </v-card>
</template>

<style scoped>
.v-data-table :deep(th) {
    white-space: nowrap;
}

.v-data-table :deep(td) {
    height: 48px !important;
}

.analytics-table {
    border-radius: 8px;
}
</style>
