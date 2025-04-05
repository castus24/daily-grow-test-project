const axiosInstance = axios.create({
    baseURL: 'http://localhost:8000', // Базовый URL для API
    timeout: 10000, // Максимальное время ожидания запроса (в миллисекундах)
    headers: {
        'Content-Type': 'application/json', // Заголовок по умолчанию
        Accept: 'application/json', // Ожидание JSON-ответов
    },
});

// Перехватчик запросов
axiosInstance.interceptors.request.use(
    (config) => {
        // Добавление токена, если он есть в localStorage
        const token = localStorage.getItem('auth_token');
        if (token) {
            config.headers['Authorization'] = `Bearer ${token}`;
        }
        return config;
    },
    (error) => {
        // Обработка ошибок запроса
        return Promise.reject(error);
    }
);

// Перехватчик ответов
axiosInstance.interceptors.response.use(
    (response) => {
        // Успешный ответ
        return response;
    },
    (error) => {
        // Обработка ошибок ответа
        if (error.response) {
            if (error.response.status === 401) {
                // Если токен недействителен, можно выполнить logout
                localStorage.removeItem('auth_token');
                // Перенаправление на страницу логина (если необходимо)
                window.location.href = '/login';
            }
        }
        return Promise.reject(error);
    }
);

export default axiosInstance;
