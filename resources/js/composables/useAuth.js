import { ref, computed } from 'vue';
import api from '../services/api';

const user = ref(null);

export function useAuth() {
  const fetchUser = async () => {
    try {
      const { data } = await api.get('/user');
      user.value = data?.user ?? null;
      return user.value;
    } catch (err) {
      // Only clear token on 401 (invalid/expired); keep token on network/5xx errors
      if (err.response?.status === 401) {
        localStorage.removeItem('token');
      }
      user.value = null;
      return null;
    }
  };

  const setUser = (u) => {
    user.value = u;
  };

  const logout = async () => {
    try {
      await api.post('/logout');
    } finally {
      localStorage.removeItem('token');
      user.value = null;
    }
  };

  const isAdmin = computed(() => !!user.value?.is_admin);

  return {
    user: computed(() => user.value),
    fetchUser,
    setUser,
    logout,
    isAdmin,
  };
}
