import { createRouter, createWebHistory } from 'vue-router';
import Login from '../views/Login.vue';
import Register from '../views/Register.vue';
import Home from '../views/Home.vue';
import GameRoom from '../views/GameRoom.vue';
import AdminCards from '../views/AdminCards.vue';
import { useAuth } from '../composables/useAuth';

const routes = [
  { path: '/', name: 'home', component: Home, meta: { requiresAuth: true } },
  { path: '/login', name: 'login', component: Login, meta: { guest: true } },
  { path: '/register', name: 'register', component: Register, meta: { guest: true } },
  { path: '/games/:id', name: 'game', component: GameRoom, meta: { requiresAuth: true } },
  { path: '/admin/cards', name: 'admin-cards', component: AdminCards, meta: { requiresAuth: true, admin: true } },
];

const router = createRouter({
  history: createWebHistory(),
  routes,
});

router.beforeEach(async (to, from, next) => {
  const { user, fetchUser } = useAuth();
  if (to.meta.requiresAuth && !user.value) {
    const token = localStorage.getItem('token');
    if (token) {
      const u = await fetchUser();
      if (!u) return next('/login');
    } else {
      return next('/login');
    }
  }
  if (to.meta.guest && user.value) {
    return next('/');
  }
  if (to.meta.admin && user.value && !user.value.is_admin) {
    return next('/');
  }
  next();
});

export default router;
