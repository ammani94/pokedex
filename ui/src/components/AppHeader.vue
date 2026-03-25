<template>
  <header class="app-header">
    <div class="header-content">
      <h1>{{ title }}</h1>
      <div class="user-actions">
          <span> Bonjour, {{ userAccount.email }}</span>
          <button @click="logout" class="logout-button">Déconnexion</button>
      </div>
      <nav>
        <ul>
          <li v-for="link in navLinks" :key="link.path">
            <router-link :to="link.path">{{ link.text }}</router-link>
          </li>
        </ul> 
      </nav>
    </div>
  </header>
</template>

<script setup>
import axios from 'axios'
import { ref, toRaw, onMounted } from 'vue'
import { useAppStore } from '../stores/user'
import { useRouter } from 'vue-router'
const router = useRouter()
const title = "Pokedex"
let PokemonInfo = ref([])
const navLinks = [
  { path: "/home", text: "Accueil"},
  { path: "/", text: "Contact" },
  { path: "/captured", text: "Capturés"},
]
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
    //console.log(result)
    if (result.success) {
      store.setUserSession({
        email: result.user.email,
        userId: result.user.id,
      })
      userAccount.value = result.user
      PokemonInfo.value = result.pokemons_count
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
  background-color: #ef5350;
  color: white;
  padding: 1rem;
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
}

.header-content {
  display: flex;
  justify-content: space-between;
  align-items: center;
  max-width: 1200px;
  margin: 0 auto;
}

nav ul {
  display: flex;
  list-style: none;
  gap: 1rem;
}

nav a {
  color: white;
  text-decoration: none;
  font-weight: bold;
}

nav a:hover {
  text-decoration: underline;
}
</style>
