<template>
  <div class="page">

    <form class="search-bar" @submit.prevent="submitForm">
      <span class="search-icon">🔍</span>
      <input v-model="search.name" class="search-input" placeholder="Rechercher un Pokémon…" required />
      <button type="submit" class="btn btn-primary">Rechercher</button>
    </form>

    <div v-if="loading" class="state">⏳ Chargement en cours…</div>
    <div v-if="error" class="state state-error">⚠️ {{ error }}</div>
    <div v-if="!loading && !error && !pokemons?.length" class="state state-empty">
      Aucun résultat. Essayez un autre nom (ex. « pikachu »).
    </div>

    <div v-if="pokemons" class="grid">
      <article v-for="p in pokemons" :key="p.name" class="card">
        <router-link :to="p.pokemon_path">
        <span class="card-id">#{{ String(p.id).padStart(3, '0') }}</span>
        <img class="card-img" :src="p.sprites.front_default" :alt="p.name" />
        <h3 class="card-name">{{ p.name }}</h3>
        <div class="card-types">
          <span v-for="t in p.types" :key="t.type.name" class="type" :class="t.type.name">
            {{ t.type.name }}
          </span>
        </div>
        <div class="card-stats">
          <span>⚖️ {{ (p.weight / 10).toFixed(1) }} kg</span>
          <span>📏 {{ (p.height / 10).toFixed(1) }} m</span>
        </div>
        <button class="btn btn-catch btn-block" @click="catchPokemons(p)">
          Capturer
        </button>
        </router-link>
      </article>
    </div>

    <nav v-if="pokemons" class="pagination">
      <button class="btn btn-ghost" :disabled="!previous" @click="fetchPokemons(previous)">
        ← Précédent
      </button>
      <button class="btn btn-ghost" :disabled="!next" @click="fetchPokemons(next)">
        Suivant →
      </button>
    </nav>

  </div>
</template>

<script setup>
import axios from 'axios'
import { ref, toRaw, onMounted,inject } from 'vue'
import { useAppStore } from '../stores/user'
const store = useAppStore()
let pokemons = ref(null)
const url = 'https://pokeapi.co/api/v2/pokemon/'
let listPokemon = ref(null)
let next = ref(null)
let previous = ref(null)
let loading = ref(false)
let error = ref(null)
let formData = ref({
    api_id: '',
    name: '',
    userId: ''
})
let search = ref({
    name: ''
})

const fetchPokemons = async (ChangeUrl) => {
      try {
        if (ChangeUrl === undefined) {
          ChangeUrl = url
        }
        const response = await axios.get(ChangeUrl)
        listPokemon.value = response.data.results
        next.value = response.data.next
        previous.value = response.data.previous
        const pokemonDetails = await Promise.all(
          listPokemon.value.map(async (pokemon) => {
            const pokemonResponse = await axios.get(pokemon.url)
            return {
                ...pokemonResponse.data,
                pokemon_path: "/pokemon/"+pokemonResponse.data.id,
            }
          })
        )
        pokemons.value = pokemonDetails
      } catch (err) {
        console.error("Erreur lors de la récupération:", err)
        error.value = "Impossible de charger les données."
      } finally {
        loading.value = false
      }
}

const catchPokemons = async (listPokemon) => {
      try {
        formData.value.api_id = listPokemon.id
        formData.value.name = listPokemon.name
        if (store.userSession.userId !== undefined) {
          formData.value.userId = store.userSession.userId
        }
        const response = await axios.post(
          'http://localhost:8080/catch',
          formData.value,
          {
            withCredentials: true,
            headers: {
              'Content-Type': 'application/json',
            },
          }
        )
        alert(response.data.message)
      } catch (error) {
        console.error("Erreur :", error)
      }
}

const submitForm = async () => {
      try {
        const response = await axios.get(url+search.value.name)
        pokemons.value = [response.data]
      } catch (error) {
        console.error("Erreur :", error)
      }
}

onMounted(fetchPokemons)
</script>

<style scoped>
.page {
  max-width: 1100px;
  margin: 0 auto;
  padding: 2rem 1.5rem;
  font-family: 'Inter', system-ui, sans-serif;
  background: #f6f7fb;
  min-height: 100vh;
}

.search-bar {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  background: #fff;
  padding: 0.5rem 0.75rem;
  border-radius: 12px;
  box-shadow: 0 1px 3px rgb(0 0 0 / 0.08);
  margin-bottom: 1.5rem;
}
.search-icon { font-size: 1rem; }
.search-input {
  flex: 1;
  border: none;
  outline: none;
  font-size: 0.95rem;
  padding: 0.5rem;
  background: transparent;
}

.grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
  gap: 1rem;
}

.card {
  position: relative;
  background: #fff;
  border-radius: 14px;
  padding: 1.25rem;
  text-align: center;
  box-shadow: 0 2px 6px rgb(0 0 0 / 0.07);
  transition: transform 0.15s, box-shadow 0.15s;
}
.card:hover {
  transform: translateY(-3px);
  box-shadow: 0 8px 20px rgb(0 0 0 / 0.12);
}
.card-id {
  position: absolute;
  top: 0.75rem;
  right: 0.9rem;
  font-size: 0.75rem;
  font-weight: 600;
  color: #9ca3af;
}
.card-img { width: 96px; height: 96px; image-rendering: pixelated; }
.card-name {
  text-transform: capitalize;
  font-size: 1rem;
  font-weight: 600;
  color: #1f2937;
  margin: 0.5rem 0;
}

.card-types { display: flex; justify-content: center; gap: 0.35rem; margin-bottom: 0.5rem; }
.type {
  font-size: 0.7rem;
  font-weight: 600;
  text-transform: uppercase;
  color: #fff;
  padding: 0.2rem 0.6rem;
  border-radius: 999px;
}
.type.grass, .type.bug { background: #78c850; }
.type.fire { background: #f08030; }
.type.water { background: #6890f0; }
.type.electric { background: #f8d030; color: #333; }
.type.poison { background: #a040a0; }
.type.psychic { background: #f85888; }
.type.normal { background: #a8a878; }
.type.flying { background: #a890f0; }
.type.rock, .type.ground { background: #b8a038; }
.type.ghost, .type.dark { background: #705848; }
.type.dragon { background: #7038f8; }
.type.steel { background: #b8b8d0; }
.type.ice { background: #98d8d8; color: #333; }
.type.fighting { background: #c03028; }

.card-stats {
  display: flex;
  justify-content: space-around;
  font-size: 0.85rem;
  color: #6b7280;
  margin-bottom: 0.75rem;
}

.btn {
  border: none;
  border-radius: 8px;
  padding: 0.5rem 0.9rem;
  font-size: 0.9rem;
  font-weight: 600;
  cursor: pointer;
  transition: filter 0.15s, transform 0.1s;
}
.btn:active { transform: scale(0.97); }
.btn:hover { filter: brightness(0.93); }
.btn:disabled { opacity: 0.4; cursor: not-allowed; }
.btn-primary { background: #6366f1; color: #fff; }
.btn-catch { background: #f59e0b; color: #fff; }
.btn-ghost { background: transparent; color: #6b7280; border: 1px solid #d1d5db; }
.btn-block { width: 100%; }

.pagination {
  display: flex;
  justify-content: center;
  gap: 0.75rem;
  margin-top: 1.5rem;
}

.state {
  text-align: center;
  padding: 2rem;
  color: #6b7280;
  background: #fff;
  border-radius: 12px;
}
.state-error { color: #ef4444; }
</style>