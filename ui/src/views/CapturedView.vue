<template>
  <button @click="isPopupOpen = true">Ouvrir la popup</button>
  <Popup v-model="isPopupOpen">
    <TeamView @team-created="handleTeamCreated" />
  </Popup>
  <div class="select_team">
    <div v-if="teams" class="team_container">
      <select>
        <option v-for="team in teams" :key="team.id" @click="() => PokemonsTeams(team.id)">{{ team.name }}</option>
      </select>
    </div>
    
  </div>
  <div>
    <div v-if="loading">Chargement en cours...</div>
    <div v-if="error" class="error">{{ error }}</div>
    <div class="container element">
      <div>Équipe :</div>
      <div class="pokemon-card" v-for="pokemon in pokemons_teams" :key="pokemon.name">
        <h2>{{ pokemon.name }} (ID: {{ pokemon.id }})</h2>
        <img :src="pokemon.sprites.front_default" :alt="pokemon.name" />
        <p>Types: {{ pokemon.types.map(t => t.type.name).join(', ') }}</p>
        <p>Poids: {{ pokemon.weight / 10 }} kg</p>
        <p>Taille: {{ pokemon.height / 10 }} m</p>
        <p>Capturé le : {{ pokemon.captured_at }} </p>
        <button @click="() => deletePokemons(pokemon.id_pokemon)"> Libérer </button>
      </div>
    </div>
    <div v-if="pokemons" class="container element" >
      <div class="pokemon-card" v-for="pokemon in pokemons" :key="pokemon.name">
        <h2>{{ pokemon.name }} (ID: {{ pokemon.id }})</h2>
        <img :src="pokemon.sprites.front_default" :alt="pokemon.name" />
        <p>Types: {{ pokemon.types.map(t => t.type.name).join(', ') }}</p>
        <p>Poids: {{ pokemon.weight / 10 }} kg</p>
        <p>Taille: {{ pokemon.height / 10 }} m</p>
        <button @click="() => deletePokemons(pokemon.id_pokemon)"> Libérer </button>
        <button @click="() => addPokemonTeam(pokemon.id_pokemon)"> + </button>
      </div>
    </div>
  </div>
</template>

<script>
import axios from 'axios'
import { ref, toRaw, onMounted, computed } from 'vue'
import { useAppStore } from '@/stores/user'

import Popup from './Popup.vue'
import TeamView from './TeamView.vue'
export default {
  components: {
    Popup, TeamView
  },
  setup() {
    const isPopupOpen = ref(false)
    const teams_id = ref(false)
    const teams = ref(null)
    const pokemons = ref(null)
    const pokemons_teams = ref(null)
    const loading = ref(null)
    const error = ref(null)
    const listPokemon = ref(null)
    const formData = ref({
        team_id: '',
        pokemon_id: ''
      })

    const PokemonsTeams = async (id) => {
      const store = useAppStore()
      teams_id.value = id
      loading.value = true
      error.value = null
      try {
        const response = await axios.post('http://localhost:8080/getPokemonsInTeams/'+id,
          {
            email: store.userSession.email,
            userId: store.userSession.userId
          }
        )
        if (response.data.success) {
            const pokemonDetails = await Promise.all(
            response.data.pokemons.map(async (pokemon) => {
              const pokemonResponse = await axios.get('https://pokeapi.co/api/v2/pokemon/'+pokemon.api_id)
              return {
                ...pokemonResponse.data,
                id_pokemon: pokemon.id,
                captured_at: pokemon.captured_at.date,
              }
            })
          )
          pokemons_teams.value = pokemonDetails
          console.log(pokemons_teams)
        } else {
          pokemons_teams.value = null
        }
        
        console.log('pokemons_teams',pokemons_teams.value)
      } catch (err) {
        console.error("Erreur lors de la récupération:", err)
        error.value = "Impossible de charger les données."
      } finally {
        loading.value = false
      }
    }

    const addPokemonTeam = async (id) => {
      formData.value.pokemon_id = id
      formData.value.team_id = teams_id.value
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
    }

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
      await PokemonsTeams()
    })

    return {
      isPopupOpen,
      pokemons_teams,
      pokemons,
      teams,
      loading,
      error,
      fetchPokemons,
      deletePokemons,
      fetchTeams,
      handleTeamCreated,
      PokemonsTeams,
      addPokemonTeam
    }
  },
};
//const store = useAppStore()
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
