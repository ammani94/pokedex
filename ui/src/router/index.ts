import { createRouter, createWebHistory } from 'vue-router'
import AuthenticationView from '../views/AuthenticationView.vue'
import SignupView from '../views/SignupView.vue'
import HomeView from '../views/HomeView.vue'
const routes = [
  { path: '/', name: 'authentification', component: AuthenticationView, meta: { hideHeader: true } },
  { path: '/signup', name: 'signup', component: SignupView, meta: { hideHeader: true } },
  { path: '/home', name: 'home', component: HomeView }
]


const router = createRouter({
  history: createWebHistory(import.meta.env.BASE_URL),
  routes,
})

export default router
