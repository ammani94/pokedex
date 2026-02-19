<script setup>
import axios from 'axios'
import { ref, toRaw } from 'vue'
import { useRouter } from 'vue-router'
const formData = ref({
  name: ''
})
const router = useRouter()
const submitCreateTeam = async () => {
  try {
        const response = await axios.post(
          'http://localhost:8080/create_team',
          formData.value,
          {
            withCredentials: true,
            headers: {
              'Content-Type': 'application/json',
            },
          }
        )
    formData.name = ''
    alert(response.data.message)
  } catch (error) {
    console.error('Erreur :', error);
    alert('Une erreur est survenue.');
  }
};
</script>

<template>
  <div class="team">
    <h1>Créer une équipe</h1>
    <form @submit.prevent="submitCreateTeam">
    <input v-model="formData.name" placeholder="Nom de l'équipe" required />
    <button type="submit">Créer l'équipe</button>
  </form>
  </div>
</template>
