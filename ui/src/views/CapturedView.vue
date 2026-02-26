<template>
  <button @click="isPopupOpen = true">Ouvrir la popup</button>
  <Popup v-model="isPopupOpen">
    <TeamView @team-created="handleTeamCreated" />
  </Popup>
  <div class="select_team">
    <div v-if="teams" class="team_container">
      <select>
        <option v-for="team in teams" :key="team.id">{{ team.name }}</option>
      </select>
    </div>
    
  </div>
  <div>
    <div v-if="loading">Chargement en cours...</div>
    <div v-if="error" class="error">{{ error }}</div>
    <div v-if="pokemons" class="container element" >
      <div class="pokemon-card" v-for="pokemon in pokemons" :key="pokemon.name">
        <h2>{{ pokemon.name }} (ID: {{ pokemon.id }})</h2>
        <img :src="pokemon.sprites.front_default" :alt="pokemon.name" />
        <p>Types: {{ pokemon.types.map(t => t.type.name).join(', ') }}</p>
        <p>Poids: {{ pokemon.weight / 10 }} kg</p>
        <p>Taille: {{ pokemon.height / 10 }} m</p>
        <button @click="() => deletePokemons(pokemon.id_pokemon)"> Libérer </button>
      </div>
    </div>
  </div>
</template>

<script>
import axios from 'axios'
import { ref, toRaw, onMounted } from 'vue'
import Popup from './Popup.vue'
import TeamView from './TeamView.vue'
export default {
  components: {
    Popup, TeamView
  },
  setup() {
    const isPopupOpen = ref(false)
    const teams = ref(null)
    const pokemons = ref(null)
    const loading = ref(null)
    const error = ref(null)
    const listPokemon = ref(null)

    const fetchPokemons = async () => {
      loading.value = true
      error.value = null
      try {
        const response = await axios.get('http://localhost:8080/getpokemons')
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
        pokemons.value = pokemonDetails
        console.log('pokemons',pokemons.value)
      } catch (err) {
        console.error("Erreur lors de la récupération:", err)
        error.value = "Impossible de charger les données."
      } finally {
        loading.value = false
      }
    }

    const deletePokemons = async (id) => {
      loading.value = true
      error.value = null
      try {
        const response = await axios.get('http://localhost:8080/deletePokemon/'+id)
        fetchPokemons()
      } catch (err) {
        console.error("Erreur lors de la récupération:", err)
        error.value = "Impossible de charger les données."
      } finally {
        loading.value = false
      }
    }

    const fetchTeams = async () => {
      loading.value = true
      error.value = null
      try {
        const response = await axios.get('http://localhost:8080/getTeams/')
        teams.value = response.data.teams
      } catch (err) {
        console.error("Erreur lors de la récupération:", err)
        error.value = "Impossible de charger les données."
      } finally {
        loading.value = false
      }
    }

    const handleTeamCreated = () => {
      isPopupOpen.value = false
      fetchTeams()
    }

    onMounted(async () => {
      await fetchPokemons()
      await fetchTeams()
    })

    return {
      isPopupOpen,
      pokemons,
      teams,
      loading,
      error,
      fetchPokemons,
      deletePokemons,
      fetchTeams,
      handleTeamCreated,
    }
  },
};
</script>

<style scoped>

.container {
  display: flex;
  flex-wrap: wrap;
}

.element {
  width: calc(70% - 10px);
  margin: 5px;
}


.pokemon-card {
  border: 1px solid #ccc;
  padding: 1rem;
  margin-top: 1rem;
  border-radius: 8px;
  max-width: 300px;
  text-align: center;
}
.error {
  color: red;
}
</style>
