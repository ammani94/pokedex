<template>
  <div class="team">
    <router-link to="/team">Créer une équipe</router-link>
  </div>
  <div class="select_team">
    <div v-if="teams" class="team_container">
      <select>
        <option v-for="listTeams in teams" :key="listTeams.id">{{ listTeams.name }}</option>
      </select>
    </div>
    
  </div>
  <div>
    <div v-if="loading">Chargement en cours...</div>
    <div v-if="error" class="error">{{ error }}</div>
    <div v-if="listPokemon" class="container element" >
      <div class="pokemon-card" v-for="listPokemons in pokemons" :key="listPokemons.name">
        <h2>{{ listPokemons.name }} (ID: {{ listPokemons.id }})</h2>
        <img :src="listPokemons.sprites.front_default" :alt="listPokemons.name" />
        <p>Types: {{ listPokemons.types.map(t => t.type.name).join(', ') }}</p>
        <p>Poids: {{ listPokemons.weight / 10 }} kg</p>
        <p>Taille: {{ listPokemons.height / 10 }} m</p>
        <button @click="() => deletePokemons(listPokemons.id_pokemon)"> Libérer </button>
      </div>
    </div>
  </div>
</template>

<script>
import axios from 'axios'
import { ref, toRaw, onMounted } from 'vue'
let isPopupOpen = ref(false)
let url = 'https://pokeapi.co/api/v2/pokemon/'
export default {
  data() {
    return {
      pokemons: null,
      teams: null,
      loading: false,
      error: null,
      formData: {
        api_id: '',
        name: ''
      }
    }
  },
  async mounted() {
    await this.fetchPokemons(),
    await this.getTeams()
  },
  methods: {
    async fetchPokemons() {
      this.loading = true
      this.error = null
      try {
        const response = await axios.get('http://localhost:8080/getpokemons')
        this.listPokemon = response.data.pokemons
        const pokemonDetails = await Promise.all(
          this.listPokemon.map(async (pokemon) => {
            const pokemonResponse = await axios.get('https://pokeapi.co/api/v2/pokemon/'+pokemon.api_id)
            const pokemonData = {
              ...pokemonResponse.data,
              id_pokemon: pokemon.id
            }
            return pokemonData
          })
        )
        this.pokemons = pokemonDetails
      } catch (err) {
        console.error("Erreur lors de la récupération:", err)
        this.error = "Impossible de charger les données."
      } finally {
        this.loading = false
      }
    },
    async deletePokemons(id) {
      this.loading = true
      this.error = null
      try {
        const response = await axios.get('http://localhost:8080/deletePokemon/'+id)
        this.listPokemon = response.data.pokemons
        
        const pokemonDetails = await Promise.all(
          this.listPokemon.map(async (pokemon) => {
            const pokemonResponse = await axios.get('https://pokeapi.co/api/v2/pokemon/'+pokemon.api_id)
            return pokemonResponse.data
          })
        )
        this.pokemons = pokemonDetails
      } catch (err) {
        console.error("Erreur lors de la récupération:", err)
        this.error = "Impossible de charger les données."
      } finally {
        this.loading = false
      }
    },
    async getTeams() {
      this.loading = true
      this.error = null
      try {
        const response = await axios.get('http://localhost:8080/getTeams/')
        this.teams = response.data.teams
        console.log(this.teams)
      } catch (err) {
        console.error("Erreur lors de la récupération:", err)
        this.error = "Impossible de charger les données."
      } finally {
        this.loading = false
      }
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
