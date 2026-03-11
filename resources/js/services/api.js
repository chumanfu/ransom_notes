import axios from 'axios';

// Use single-path gateway so hosts that block /api or /v1 still reach Laravel
const api = axios.create({
  baseURL: '/g',
  headers: {
    'Content-Type': 'application/json',
    'Accept': 'application/json',
  },
});

api.interceptors.request.use((config) => {
  const token = localStorage.getItem('token');
  if (token) {
    config.headers.Authorization = `Bearer ${token}`;
  }
  // Tell gateway which API path to dispatch (e.g. /v1/login)
  const path = config.url ?? '';
  config.headers['X-API-Path'] = path.startsWith('/') ? path : `/${path}`;
  config.headers['X-API-Path'] = config.headers['X-API-Path'].startsWith('/v1') ? config.headers['X-API-Path'] : `/v1${config.headers['X-API-Path']}`;
  config.url = '';
  return config;
});

api.interceptors.response.use(
  (r) => r,
  (err) => {
    if (err.response?.status === 401) {
      localStorage.removeItem('token');
      window.location.href = '/login';
    }
    return Promise.reject(err);
  }
);

export default api;
