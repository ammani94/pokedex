<template>
  <div class="page">

    <div v-if="loading" class="state">⏳ Chargement en cours…</div>
    <div v-if="error" class="state state-error">⚠️ {{ error }}</div>

    <div v-if="pokemons" class="sheet">

      <aside class="hero">
        <span class="hero-id">#{{ String(pokemons.id).padStart(4, '0') }}</span>
        <div class="hero-sprite">
          <img :src="sprite" \:alt="pokemons.name" />
        </div>
        <h1 class="hero-name">{{ pokemons.name }}</h1>
        <div class="card-types">
          <span v-for="t in pokemons.types" \:key="t.type.name" class="type" \:class="t.type.name">
            {{ t.type.name }}
          </span>
        </div>

        <button
          v-if="pokemons.sprites.front_shiny"
          class="btn btn-ghost btn-block"
          @click="shiny = !shiny"
        >
          {{ shiny ? '✨ Vue normale' : '✨ Vue shiny' }}
        </button>
        <button class="btn btn-catch btn-block" @click="catchPokemons(pokemons)">
          Capturer
        </button>
      </aside>

      <section class="details">

        <div class="metrics">
          <div class="metric">
            <span class="metric-label">Poids</span>
            <span class="metric-value">{{ (pokemons.weight / 10).toFixed(1) }} kg</span>
          </div>
          <div class="metric">
            <span class="metric-label">Taille</span>
            <span class="metric-value">{{ (pokemons.height / 10).toFixed(1) }} m</span>
          </div>
          <div class="metric">
            <span class="metric-label">Exp. de base</span>
            <span class="metric-value">{{ pokemons.base_experience ?? '—' }}</span>
          </div>
        </div>

        <div class="panel">
          <h2 class="panel-title">Stats de base</h2>
          <div class="stat-row" v-for="s in pokemons.stats" :key="s.stat.name">
            <span class="stat-name">{{ s.stat.name }}</span>
            <div class="stat-bar">
              <div
                class="stat-fill"
                \:class="statLevel(s.base_stat)"
                \:style="{ width: Math.min(s.base_stat / 255 * 100, 100) + '%' }"
              ></div>
            </div>
            <span class="stat-value">{{ s.base_stat }}</span>
          </div>
        </div>

        <div class="panel">
          <h2 class="panel-title">Capacités</h2>
          <div class="chips">
            <span v-for="a in pokemons.abilities" \:key="a.ability.name" class="chip">
              {{ a.ability.name }}
            </span>
          </div>
        </div>

        <div class="panel" v-if="pokemons.moves?.length">
          <h2 class="panel-title">Attaques <small>({{ pokemons.moves.length }} au total)</small></h2>
          <div class="chips">
            <span v-for="m in pokemons.moves.slice(0, 12)" :key="m.move.name" class="chip chip-move">
              {{ m.move.name }}
            </span>
          </div>
        </div>

      </section>
    </div>

  </div>
</template>

<script setup>
import axios from 'axios'
import { ref, toRaw, onMounted, computed } from 'vue'
import { useAppStore } from '../stores/user'
const store = useAppStore()
let pokemons = ref(null)
import { useRoute } from 'vue-router'
const route = useRoute()
const id = ref(route.params.id)
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
let shiny = ref(false)

const sprite = computed(() =>
  shiny.value
    ? (pokemons.value?.sprites?.front_shiny ?? pokemons.value?.sprites?.front_default)
    : pokemons.value?.sprites?.front_default
)

const statLevel = (v) =>
  v >= 120 ? 'stat-high' : v >= 70 ? 'stat-mid' : 'stat-low'

const fetchPokemons = async () => {
      try {
        const response = await axios.get(url+id.value)
        pokemons.value = response.data
        console.log(pokemons.value)
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
  max-width: 1000px;
  margin: 0 auto;
  padding: 2rem 1.5rem;
  font-family: 'Inter', system-ui, sans-serif;
  background: #f6f7fb;
  min-height: 100vh;
}

.sheet {
  display: grid;
  grid-template-columns: 320px 1fr;
  gap: 1.5rem;
  align-items: start;
}

.hero {
  background: #fff;
  border-radius: 18px;
  padding: 1.75rem 1.5rem;
  text-align: center;
  box-shadow: 0 2px 8px rgb(0 0 0 / 0.08);
  display: flex;
  flex-direction: column;
  gap: 0.75rem;
  position: sticky;
  top: 5.5rem;
}
.hero-id { font-weight: 700; color: #9ca3af; font-size: 0.9rem; }
.hero-sprite img {
  width: 180px;
  height: 180px;
  image-rendering: pixelated;
}
.hero-name {
  text-transform: capitalize;
  font-size: 1.6rem;
  font-weight: 800;
  color: #1f2937;
  margin: 0;
}

.card-types { display: flex; justify-content: center; gap: 0.4rem; }
.type {
  font-size: 0.75rem;
  font-weight: 600;
  text-transform: uppercase;
  color: #fff;
  padding: 0.25rem 0.7rem;
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

.details { display: flex; flex-direction: column; gap: 1.25rem; }

.metrics {
  display: grid;
  grid-template-columns: repeat(3, 1fr);
  gap: 1rem;
}
.metric {
  background: #fff;
  border-radius: 12px;
  padding: 0.9rem 1rem;
  text-align: center;
  box-shadow: 0 2px 6px rgb(0 0 0 / 0.07);
}
.metric-label {
  display: block;
  font-size: 0.75rem;
  color: #9ca3af;
  text-transform: uppercase;
  letter-spacing: 0.5px;
}
.metric-value { font-size: 1.15rem; font-weight: 700; color: #1f2937; }

.panel {
  background: #fff;
  border-radius: 14px;
  padding: 1.25rem 1.5rem;
  box-shadow: 0 2px 6px rgb(0 0 0 / 0.07);
}
.panel-title {
  font-size: 1rem;
  font-weight: 700;
  color: #1f2937;
  margin: 0 0 1rem;
}
.panel-title small { color: #9ca3af; font-weight: 500; }

.stat-row {
  display: grid;
  grid-template-columns: 110px 1fr 48px;
  align-items: center;
  gap: 0.75rem;
  margin-bottom: 0.6rem;
}
.stat-name {
  font-size: 0.85rem;
  color: #6b7280;
  text-transform: capitalize;
}
.stat-bar {
  height: 8px;
  background: #eef0f4;
  border-radius: 999px;
  overflow: hidden;
}
.stat-fill {
  height: 100%;
  border-radius: 999px;
  transition: width 0.4s ease;
}
.stat-low  { background: #ef4444; }
.stat-mid  { background: #f59e0b; }
.stat-high { background: #22c55e; }
.stat-value {
  font-size: 0.85rem;
  font-weight: 700;
  color: #374151;
  text-align: right;
}

.chips { display: flex; flex-wrap: wrap; gap: 0.4rem; }
.chip {
  background: #eef2ff;
  color: #6366f1;
  font-size: 0.8rem;
  font-weight: 600;
  padding: 0.3rem 0.7rem;
  border-radius: 999px;
}
.chip-move { background: #f0fdf4; color: #16a34a; }

.btn {
  border: none;
  border-radius: 10px;
  padding: 0.6rem 1rem;
  font-size: 0.9rem;
  font-weight: 600;
  cursor: pointer;
  transition: filter 0.15s, transform 0.1s;
}
.btn:active { transform: scale(0.97); }
.btn:hover { filter: brightness(0.93); }
.btn-catch { background: #f59e0b; color: #fff; }
.btn-ghost { background: transparent; color: #6b7280; border: 1px solid #d1d5db; }
.btn-block { width: 100%; }

.state {
  text-align: center;
  padding: 2rem;
  color: #6b7280;
  background: #fff;
  border-radius: 12px;
}
.state-error { color: #ef4444; }

@media (max-width: 800px) {
  .sheet { grid-template-columns: 1fr; }
  .hero { position: static; }
  .metrics { grid-template-columns: repeat(3, 1fr); gap: 0.5rem; }
}
</style>