<template>
  <div class="page">

    <header class="page-header">
      <div>
        <h1 class="title">Mon Pokédex</h1>
        <p class="subtitle">Gérez vos captures et vos équipes</p>
      </div>
      <button class="btn btn-primary" @click="isPopupOpen = true">
        <span class="btn-icon">+</span> Créer une équipe
      </button>
    </header>

    <Popup v-model="isPopupOpen">
      <TeamView @team-created="handleTeamCreated" />
    </Popup>

    <section class="team-bar">
      <label class="team-label" for="team-select">Équipe</label>
      <select id="team-select" class="select" @change="fetchPokemonsInTeams($event.target.value)">
        <option v-for="team in teams" :key="team.name" :value="team.id">
          {{ team.name }}
        </option>
      </select>
    </section>

    <div v-if="loading" class="state">⏳ Chargement en cours…</div>
    <div v-if="error" class="state state-error">⚠️ {{ error }}</div>

    <section v-if="pokemonsTeams?.length" class="section">
      <h2 class="section-title">
        Équipe <span class="badge badge-team">{{ pokemonsTeams.length }}</span>
      </h2>
      <div class="grid">
        <article v-for="pokemon in pokemonsTeams" :key="pokemon.name" class="card">
          <img class="card-img" :src="pokemon.sprites.front_default" :alt="pokemon.name" />
          <h3 class="card-name">{{ pokemon.name }}</h3>
          <div class="card-types">
            <span v-for="t in pokemon.types" :key="t.type.name" class="type" :class="t.type.name">
              {{ t.type.name }}
            </span>
          </div>
          <div class="card-stats">
            <span>⚖️ {{ (pokemon.weight / 10).toFixed(1) }} kg</span>
            <span>📏 {{ (pokemon.height / 10).toFixed(1) }} m</span>
          </div>
          <button class="btn btn-danger btn-block" @click="PokemonsOutOfTeam(pokemon.id_pokemon)">
            Quitter
          </button>
        </article>
      </div>
    </section>

    <section class="section">
      <h2 class="section-title">Pokémons capturés sans équipe</h2>
      <div v-if="!pokemons?.length && !loading" class="state state-empty">
        Aucun Pokémon capturé pour le moment.
      </div>
      <div v-if="pokemons" class="grid">
        <article v-for="pokemon in pokemons" :key="pokemon.name" class="card">
          <img class="card-img" :src="pokemon.sprites.front_default" :alt="pokemon.name" />
          <h3 class="card-name">{{ pokemon.name }}</h3>
          <div class="card-types">
            <span v-for="t in pokemon.types" :key="t.type.name" class="type" :class="t.type.name">
              {{ t.type.name }}
            </span>
          </div>
          <div class="card-stats">
            <span>⚖️ {{ (pokemon.weight / 10).toFixed(1) }} kg</span>
            <span>📏 {{ (pokemon.height / 10).toFixed(1) }} m</span>
          </div>
          <div class="card-actions">
            <button class="btn btn-primary" @click="addPokemonToTeam(pokemon.id_pokemon)">+</button>
            <button class="btn btn-ghost" @click="freePokemons(pokemon.id_pokemon)">Libérer</button>
          </div>
        </article>
      </div>
    </section>

  </div>
</template>

<script setup>
import axios from 'axios'
import { ref, toRaw, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { useAppStore } from '../stores/user'
import Popup from './Popup.vue'
import TeamView from './TeamView.vue'
let store = useAppStore()
let isPopupOpen = ref(false)
let pokemons = ref(null)
let pokemonsTeams = ref(null)
let listPokemon = ref(null)
let teams = ref(null)
let loading = ref(null)
let error = ref(null)
let formData = ref({
        id_pokemon: ''
      })
let idTeam = ref(null)

const setIdTeam = async(id) => {
      idTeam.value = id;
    }

const fetchPokemonsInTeams = async(id) => {
    if (id !== undefined) {
        setIdTeam(id)
      try {
        const response = await axios.post('http://localhost:8080/pokemons/team/'+id, {
          user_id:store.userSession.userId
        }, {
          withCredentials: true
        })
        listPokemon.value = response.data.pokemons
        const pokemonDetails = await Promise.all(
          listPokemon.value.map(async (pokemon) => {
            const pokemonResponse = await axios.get('https://pokeapi.co/api/v2/pokemon/'+pokemon.api_id)
            return {
                ...pokemonResponse.data,
                id_pokemon: pokemon.id,
            }
          })
        )
        pokemonsTeams.value = pokemonDetails
      } catch (err) {
        console.error("Erreur lors de la récupération:", err)
        error = "Impossible de charger les données."
      } finally {
        loading = false
      }
    }
      
    }


 const addPokemonToTeam = async(pokemon_id) => {
      formData.value.pokemon_id = pokemon_id
      formData.value.team_id = idTeam.value
      formData.value.user_id = store.userSession.userId
      if (formData.value.team_id == false) {
        formData.value.team_id = teams.value[0].id
      }
      const response = await axios.post(
          'http://localhost:8080/addPokemonTeam',
          formData.value,
          {
            withCredentials: true,
            headers: {
              'Content-Type': 'application/json',
            },
          }
      )
      alert(response.data.message)
      fetchPokemons()
      fetchPokemonsInTeams(idTeam.value)
  }

const PokemonsOutOfTeam = async(pokemon_id) => {
  const response = await axios.post('http://localhost:8080/team/'+idTeam.value+'/pokemon/'+pokemon_id, {
          user_id:store.userSession.userId
        }, {
          withCredentials: true
        })
        alert(response.data.message)
        fetchPokemons()
        fetchPokemonsInTeams(idTeam.value)
    }

const fetchPokemons = async() => {
      loading = true
      error = null
      try {
        const response = await axios.post('http://localhost:8080/getpokemons', {
          user_id:store.userSession.userId
        }, {
          withCredentials: true
        })
        listPokemon.value = response.data.pokemons
        console.log(listPokemon.value)
        const pokemonDetails = await Promise.all(
          listPokemon.value.map(async (pokemon) => {
            const pokemonResponse = await axios.get('https://pokeapi.co/api/v2/pokemon/'+pokemon.api_id)
            return {
                ...pokemonResponse.data,
                id_pokemon: pokemon.id,
            }
          })
        )
        pokemons.value = pokemonDetails
      } catch (err) {
        console.error("Erreur lors de la récupération:", err)
        error = "Impossible de charger les données."
      } finally {
        loading = false
      }
    }
    const freePokemons = async(id) => {
      loading = true
      error = null
      try {
        const response = await axios.post('http://localhost:8080/deletePokemon/'+id, {
          user_id:store.userSession.userId
        }, {
          withCredentials: true
        })
        alert(response.data.message)
        fetchPokemons()
      } catch (err) {
        console.error("Erreur lors de la récupération:", err)
        error = "Impossible de charger les données."
      } finally {
        loading = false
      }
    }

    const fetchTeams = async() => {
      loading = true
      error = null
      try {
        const response = await axios.post('http://localhost:8080/getTeams', {
          user_id:store.userSession.userId
        }, {
          withCredentials: true
        })
        teams.value = response.data.teams
        fetchPokemonsInTeams(teams.value[0].id)
      } catch (err) {
        console.error("Erreur lors de la récupération:", err)
        error = "Impossible de charger les données."
      } finally {
        loading = false
      }
    }

    const handleTeamCreated = () => {
      isPopupOpen.value = false
      fetchTeams()
    }
    
    onMounted(fetchPokemons)
    onMounted(fetchTeams)
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

.page-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  gap: 1rem;
  margin-bottom: 1.5rem;
}
.title { font-size: 1.75rem; font-weight: 700; color: #1a1a2e; margin: 0; }
.subtitle { color: #6b7280; margin: 0.25rem 0 0; }

.team-bar {
  display: flex;
  align-items: center;
  gap: 0.75rem;
  background: #fff;
  padding: 0.75rem 1rem;
  border-radius: 12px;
  box-shadow: 0 1px 3px rgb(0 0 0 / 0.08);
  margin-bottom: 1.5rem;
}
.team-label { font-weight: 600; color: #374151; font-size: 0.9rem; }
.select {
  padding: 0.5rem 1rem;
  border-radius: 8px;
  border: 1px solid #d1d5db;
  background: #f9fafb;
  font-size: 0.95rem;
  cursor: pointer;
  transition: border-color 0.15s;
}
.select:hover { border-color: #9ca3af; }

.section { margin-bottom: 2rem; }
.section-title {
  font-size: 1.15rem;
  color: #1a1a2e;
  display: flex;
  align-items: center;
  gap: 0.5rem;
}
.grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
  gap: 1rem;
}

.card {
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
.btn-primary { background: #6366f1; color: #fff; }
.btn-danger { background: #ef4444; color: #fff; }
.btn-ghost { background: transparent; color: #6b7280; border: 1px solid #d1d5db; }
.btn-icon { font-size: 1.1rem; margin-right: 0.25rem; }
.btn-block { width: 100%; }
.card-actions { display: flex; gap: 0.5rem; }
.card-actions .btn:first-child { flex: 1; }

.state {
  text-align: center;
  padding: 2rem;
  color: #6b7280;
  background: #fff;
  border-radius: 12px;
}
.state-error { color: #ef4444; }
.badge {
  font-size: 0.75rem;
  padding: 0.15rem 0.5rem;
  border-radius: 999px;
  background: #eef2ff;
  color: #6366f1;
}
</style>


