<template>
  <header class="app-header">
    <div class="header-content">

      <div class="brand">
        <span class="brand-ball">⚪</span>
        <h1 class="brand-title">{{ title }}</h1>
      </div>

      <nav class="nav">
        <router-link
          v-for="link in navLinks"
          :key="link.path"
          :to="link.path"
          class="nav-link"
        >
          {{ link.text }}
        </router-link>
      </nav>

      <div class="user-actions">
        <div class="user-chip" :title="userAccount.email">
          <span class="avatar">{{ initials }}</span>
          <span class="user-info">
            <span class="user-hello">Bonjour,</span>
            <span class="user-email">{{ userAccount.email }}</span>
          </span>
          <span v-if="pokemonCount !== null" class="poke-badge">poké {{ pokemonCount }}</span>
        </div>
        <button class="logout-button" @click="logout">Déconnexion</button>
      </div>

    </div>
  </header>
</template>

<script setup>
import axios from 'axios'
import { ref, computed, onMounted } from 'vue'
import { useAppStore } from '../stores/user'
import { useRouter } from 'vue-router'
const router = useRouter()
const title = "Pokedex"
let pokemonCount = ref(null)
const navLinks = [
  { path: "/home", text: "Accueil"},
  { path: "/captured", text: "Capturés"},
  { path: "/ia", text: "IA"},
]
const initials = computed(() =>
  (userAccount.value?.email?.[0] ?? '?').toUpperCase()
)
let userAccount = ref([])
const store = useAppStore()
const user = async () => {
  try {
    const response = await axios.get(
          'http://localhost:8080/user',
          {
            withCredentials: true,
            headers: {
              'Content-Type': 'application/json',
            },
          }
        )
    const result = await response.data
    if (result.success) {
      store.setUserSession({
        email: result.user.email,
        userId: result.user.id,
      })
      userAccount.value = result.user
      pokemonCount.value = result.pokemons_count
    } else {
      router.push({name: 'authentification'})
    }
  } catch (error) {
    console.error('Erreur lors de la récupération des données :', error)
  }
}

const logout = async () => {
  try {
    const response = await fetch('http://localhost:8080/logout', {
      method: 'POST',
      credentials: 'include',
      headers: {
        'Content-Type': 'application/json',
      }
    })

    const result = await response.json()
    if (result.success) {
        router.push({name: 'authentification'})
    }
  } catch (error) {
    console.error('Erreur :', error);
    alert('Une erreur est survenue.');
  }
};


onMounted(user)
</script>

<style scoped>
.app-header {
  position: sticky;
  top: 0;
  z-index: 50;
  background: rgb(239 83 80 / 0.92);
  backdrop-filter: blur(8px);
  color: #fff;
  box-shadow: 0 2px 12px rgb(0 0 0 / 0.12);
}

.header-content {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 1.5rem;
  max-width: 1200px;
  margin: 0 auto;
  padding: 0.75rem 1.5rem;
}

.brand { display: flex; align-items: center; gap: 0.5rem; }
.brand-ball { font-size: 1.4rem; }
.brand-title {
  font-size: 1.25rem;
  font-weight: 800;
  letter-spacing: 0.5px;
  margin: 0;
}

.nav {
  display: flex;
  gap: 0.25rem;
  background: rgb(0 0 0 / 0.15);
  padding: 0.25rem;
  border-radius: 999px;
}
.nav-link {
  color: rgb(255 255 255 / 0.85);
  text-decoration: none;
  font-weight: 600;
  font-size: 0.9rem;
  padding: 0.4rem 1rem;
  border-radius: 999px;
  transition: background 0.15s, color 0.15s;
}
.nav-link:hover {
  background: rgb(255 255 255 / 0.15);
  color: #fff;
}

.nav-link.router-link-active {
  background: #fff;
  color: #ef5350;
}

.user-actions {
  display: flex;
  align-items: center;
  gap: 0.75rem;
}
.user-chip {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  background: rgb(255 255 255 / 0.15);
  padding: 0.35rem 0.75rem;
  border-radius: 999px;
}
.avatar {
  display: grid;
  place-items: center;
  width: 30px;
  height: 30px;
  border-radius: 50%;
  background: #fff;
  color: #ef5350;
  font-weight: 700;
  font-size: 0.8rem;
}
.user-info {
  display: flex;
  flex-direction: column;
  line-height: 1.2;
}
.user-hello { font-size: 0.65rem; opacity: 0.8; }
.user-email {
  font-size: 0.8rem;
  font-weight: 600;
  max-width: 160px;
  overflow: hidden;
  text-overflow: ellipsis;
  white-space: nowrap;
}
.poke-badge {
  font-size: 0.7rem;
  font-weight: 700;
  background: #fbbf24;
  color: #7c2d12;
  padding: 0.15rem 0.5rem;
  border-radius: 999px;
}

.logout-button {
  border: 1px solid rgb(255 255 255 / 0.5);
  background: transparent;
  color: #fff;
  font-weight: 600;
  font-size: 0.85rem;
  padding: 0.45rem 1rem;
  border-radius: 8px;
  cursor: pointer;
  transition: background 0.15s;
}
.logout-button:hover { background: rgb(255 255 255 / 0.2); }

@media (max-width: 768px) {
  .user-info, .poke-badge { display: none; }
  .header-content { flex-wrap: wrap; }
}
</style>
