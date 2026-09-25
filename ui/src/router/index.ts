import { createRouter, createWebHistory } from 'vue-router'
import AuthenticationView from '../views/AuthenticationView.vue'
import SignupView from '../views/SignupView.vue'
import HomeView from '../views/HomeView.vue'
import CapturedView from '../views/CapturedView.vue'
import TeamView from '../views/TeamView.vue'
import Ia from '../views/IaView.vue'
import PokemonView from '../views/PokemonView.vue'
const routes = [
  { path: '/', name: 'authentification', component: AuthenticationView, meta: { hideHeader: true } },
  { path: '/signup', name: 'signup', component: SignupView, meta: { hideHeader: true } },
  { path: '/captured', name: 'captured', component: CapturedView },
  { path: '/team', name: 'team', component: TeamView },
  { path: '/home', name: 'home', component: HomeView },
  { path: '/ia', name: 'ia', component: Ia },
  { path: '/pokemon/:id', name: 'pokemon', component: PokemonView }
]


const router = createRouter({
  history: createWebHistory(import.meta.env.BASE_URL),
  routes,
})

export default router
