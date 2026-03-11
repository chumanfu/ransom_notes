import axios from 'axios';

// All API requests go to "/" (only URL that reaches Laravel on IONOS) with X-API-Path header
const api = axios.create({
  baseURL: '/',
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
  // Request goes to "/"; gateway dispatches using this header (e.g. /v1/login)
  const path = (config.url ?? '').replace(/^\//, '');
  config.headers['X-API-Path'] = path.startsWith('v1/') ? `/${path}` : `/v1/${path}`;
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
