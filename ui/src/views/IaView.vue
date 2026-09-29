<template>
  <div class="page">

    <header class="chat-header">
      <div class="brand">
        <span class="brand-logo">🤖</span>
        <div>
          <h1 class="brand-title">Mistral AI</h1>
          <p class="brand-sub">Posez votre question, obtenez une réponse</p>
        </div>
      </div>
      <button v-if="messages.length" class="btn btn-ghost" @click="clearChat">
        ✕ Effacer
      </button>
    </header>

    <div class="chat" ref="chatBox">
      <div v-if="!messages.length && !loading" class="state state-empty">
        <span class="state-icon">💬</span>
        Aucune conversation pour le moment.<br />
        Commencez par écrire vos instructions ci-dessous.
      </div>

      <template v-for="(msg, i) in messages" \:key="i">
        <div class="row row-user">
          <div class="bubble bubble-user">{{ msg.query }}</div>
          <span class="avatar avatar-user">Moi</span>
        </div>

        <div v-if="msg.answer || msg.error" class="row row-ia">
          <span class="avatar avatar-ia">AI</span>
          <div class="bubble bubble-ia" \:class="{ 'bubble-error': msg.error }">
            {{ msg.answer || msg.error }}
          </div>
        </div>
      </template>

      <div v-if="loading" class="row row-ia">
        <span class="avatar avatar-ia">AI</span>
        <div class="bubble bubble-ia typing">
          <span class="dot"></span><span class="dot"></span><span class="dot"></span>
        </div>
      </div>
    </div>

    <form class="composer" @submit.prevent="submitForm">
      <textarea
        v-model="formIa.query"
        class="composer-input"
        placeholder="Instructions…"
        rows="1"
        required
        @keydown.enter.exact.prevent="submitForm"
      ></textarea>
      <button type="submit" class="btn btn-send" \:disabled="loading || !formIa.query.trim()">
        <span v-if="!loading">Envoyer ➤</span>
        <span v-else class="spinner"></span>
      </button>
    </form>

  </div>
</template>

<script setup>
import { ref, nextTick } from 'vue'
import { callMistralAPI } from '../api/mistral'

const messages = ref([])
const loading = ref(false)
const chatBox = ref(null)
const formIa = ref({ query: '' })

const scrollToBottom = async () => {
  await nextTick()
  chatBox.value?.scrollTo({ top: chatBox.value.scrollHeight, behavior: 'smooth' })
}

const submitForm = async () => {
  const query = formIa.value.query.trim()
  if (!query || loading.value) return

  messages.value.push({ query, answer: '', error: '' })
  formIa.value.query = ''
  loading.value = true
  scrollToBottom()

  try {
    const answer = await callMistralAPI(query)
    messages.value[messages.value.length - 1].answer = answer
  } catch (e) {
    messages.value[messages.value.length - 1].error = 'Erreur de chargement.'
  } finally {
    loading.value = false
    scrollToBottom()
  }
}

const clearChat = () => {
  messages.value = []
}
</script>

<style scoped>
.page {
  max-width: 820px;
  margin: 0 auto;
  padding: 2rem 1.5rem;
  font-family: 'Inter', system-ui, sans-serif;
  background: #f6f7fb;
  min-height: 100vh;
  display: flex;
  flex-direction: column;
}

.chat-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 1.25rem;
}
.brand { display: flex; align-items: center; gap: 0.75rem; }
.brand-logo {
  display: grid;
  place-items: center;
  width: 44px;
  height: 44px;
  border-radius: 12px;
  background: linear-gradient(135deg, #ff7000, #ffd200);
  font-size: 1.4rem;
}
.brand-title { font-size: 1.3rem; font-weight: 800; color: #1f2937; margin: 0; }
.brand-sub { color: #6b7280; font-size: 0.85rem; margin: 0.15rem 0 0; }

.chat {
  flex: 1;
  overflow-y: auto;
  background: #fff;
  border-radius: 16px;
  padding: 1.25rem;
  box-shadow: 0 2px 8px rgb(0 0 0 / 0.07);
  display: flex;
  flex-direction: column;
  gap: 0.9rem;
  min-height: 300px;
  max-height: 60vh;
}

.row { display: flex; align-items: flex-end; gap: 0.5rem; }
.row-user { justify-content: flex-end; }
.row-ia { justify-content: flex-start; }

.avatar {
  flex-shrink: 0;
  display: grid;
  place-items: center;
  width: 32px;
  height: 32px;
  border-radius: 50%;
  font-size: 0.7rem;
  font-weight: 700;
}
.avatar-user { background: #6366f1; color: #fff; }
.avatar-ia { background: linear-gradient(135deg, #ff7000, #ffd200); color: #7c2d12; }

.bubble {
  max-width: 75%;
  padding: 0.7rem 1rem;
  border-radius: 14px;
  font-size: 0.92rem;
  line-height: 1.5;
  white-space: pre-wrap;
  word-break: break-word;
}
.bubble-user {
  background: #6366f1;
  color: #fff;
  border-bottom-right-radius: 4px;
}
.bubble-ia {
  background: #f3f4f6;
  color: #1f2937;
  border-bottom-left-radius: 4px;
}
.bubble-error { background: #fef2f2; color: #ef4444; }

.typing { display: flex; gap: 5px; align-items: center; }
.dot {
  width: 7px;
  height: 7px;
  border-radius: 50%;
  background: #9ca3af;
  animation: blink 1.2s infinite;
}
.dot:nth-child(2) { animation-delay: 0.2s; }
.dot:nth-child(3) { animation-delay: 0.4s; }
@keyframes blink {
  0%, 80%, 100% { opacity: 0.3; }
  40% { opacity: 1; }
}

.composer {
  display: flex;
  align-items: flex-end;
  gap: 0.5rem;
  background: #fff;
  padding: 0.6rem;
  border-radius: 14px;
  box-shadow: 0 2px 8px rgb(0 0 0 / 0.07);
  margin-top: 1rem;
}
.composer-input {
  flex: 1;
  border: none;
  outline: none;
  resize: none;
  font-size: 0.95rem;
  font-family: inherit;
  padding: 0.5rem 0.6rem;
  max-height: 150px;
  line-height: 1.5;
}

.btn {
  border: none;
  border-radius: 10px;
  padding: 0.5rem 0.9rem;
  font-size: 0.9rem;
  font-weight: 600;
  cursor: pointer;
  transition: filter 0.15s, transform 0.1s;
}
.btn:active { transform: scale(0.97); }
.btn:hover { filter: brightness(0.93); }
.btn-ghost { background: transparent; color: #6b7280; border: 1px solid #d1d5db; }
.btn-send {
  background: #6366f1;
  color: #fff;
  padding: 0.6rem 1.1rem;
  display: flex;
  align-items: center;
}
.btn-send:disabled { opacity: 0.5; cursor: not-allowed; }

.spinner {
  width: 16px;
  height: 16px;
  border: 2px solid rgb(255 255 255 / 0.4);
  border-top-color: #fff;
  border-radius: 50%;
  animation: spin 0.8s linear infinite;
}
@keyframes spin { to { transform: rotate(360deg); } }

.state {
  text-align: center;
  padding: 2.5rem 1rem;
  color: #9ca3af;
  font-size: 0.95rem;
}
.state-icon { font-size: 2rem; display: block; margin-bottom: 0.5rem; }
</style>