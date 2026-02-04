<template>
    <div :class="[embedded ? 'w-full h-full flex flex-col overflow-hidden' : 'fixed bottom-8 right-8 z-50']">
        <!-- Chat Toggle Button (Only if NOT embedded) -->
        <button v-if="!embedded" @click="toggleChat"
            class="bg-blue-600 hover:bg-blue-700 text-white p-4 rounded-full shadow-2xl transition-all duration-300 transform hover:scale-110 active:scale-95 flex items-center justify-center"
            style="width: 60px; height: 60px;">
            <svg v-if="!isOpen" xmlns="http://www.w3.org/2000/svg" class="h-8 w-8" fill="none" viewBox="0 0 24 24"
                stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z" />
            </svg>
            <svg v-else xmlns="http://www.w3.org/2000/svg" class="h-8 w-8" fill="none" viewBox="0 0 24 24"
                stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
        </button>

        <!-- Chat Window -->
        <component :is="embedded ? 'div' : 'transition'" v-bind="embedded ? {} : { name: 'fade-slide' }">
            <div v-if="embedded || isOpen" :class="[
                embedded ? 'flex flex-col h-full w-full' : 'absolute bottom-20 right-0 w-[400px] max-w-[90vw] h-[600px] max-h-[70vh] bg-[#0a0a0a]/90 backdrop-blur-xl border border-white/10 rounded-2xl shadow-2xl flex flex-col overflow-hidden'
            ]">
                <!-- Header (Optional if embedded? Maybe keep it) -->
                <div v-if="!embedded"
                    class="p-4 bg-blue-600/10 border-b border-white/10 flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-full bg-blue-600 flex items-center justify-center font-bold">S
                        </div>
                        <div>
                            <h3 class="font-bold text-white text-sm">Assistant ShiftUp</h3>
                            <p class="text-[10px] text-blue-400">En ligne • Propulsé par Gemini</p>
                        </div>
                    </div>
                </div>

                <!-- Messages -->
                <div class="flex-1 overflow-y-auto p-4 space-y-4 pt-1" ref="messageContainer">
                    <div v-for="(msg, index) in messages" :key="index"
                        :class="['flex', msg.role === 'user' ? 'justify-end' : 'justify-start']">
                        <div :class="[
                            'max-w-[85%] p-3 rounded-2xl text-[0.85rem] leading-relaxed',
                            msg.role === 'user' ? 'bg-blue-600/80 text-white rounded-tr-none' : 'bg-white/10 border border-white/5 text-gray-200 rounded-tl-none backdrop-blur-md'
                        ]">
                            <div class="markdown-body" v-html="renderMarkdown(msg.content)"></div>
                        </div>
                    </div>
                    <div v-if="isLoading" class="flex justify-start">
                        <div class="bg-white/5 border border-white/10 p-3 rounded-2xl rounded-tl-none flex gap-1">
                            <span class="w-1.5 h-1.5 bg-gray-500 rounded-full animate-bounce"
                                style="animation-delay: 0ms"></span>
                            <span class="w-1.5 h-1.5 bg-gray-500 rounded-full animate-bounce"
                                style="animation-delay: 150ms"></span>
                            <span class="w-1.5 h-1.5 bg-gray-500 rounded-full animate-bounce"
                                style="animation-delay: 300ms"></span>
                        </div>
                    </div>
                </div>

                <!-- Input -->
                <div :class="[embedded ? 'p-2' : 'p-4', 'border-t border-white/10 bg-white/5']">
                    <form @submit.prevent="sendMessage" class="flex gap-2">
                        <input v-model="userInput" type="text" placeholder="Posez votre question..."
                            class="flex-1 bg-black/40 border border-white/10 rounded-xl px-3 py-1.5 text-[0.7rem] text-white focus:outline-none focus:border-blue-500 transition-colors"
                            :disabled="isLoading">
                        <button type="submit"
                            class="bg-blue-600 hover:bg-blue-700 p-1.5 rounded-xl text-white disabled:opacity-50 disabled:cursor-not-allowed transition-all"
                            :disabled="isLoading || !userInput.trim()">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24"
                                stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8" />
                            </svg>
                        </button>
                    </form>
                </div>
            </div>
        </component>
    </div>
</template>

<script setup>
import { ref, onMounted, nextTick } from 'vue';
import axios from 'axios';
import { marked } from 'marked';

const props = defineProps({
    embedded: {
        type: Boolean,
        default: false
    }
});

const isOpen = ref(false);
const isLoading = ref(false);
const userInput = ref('');
const messageContainer = ref(null);
const messages = ref([
    { role: 'assistant', content: 'Bonjour ! Je suis votre assistant ShiftUp. Comment puis-je vous aider aujourd\'hui ?' }
]);

const toggleChat = () => {
    isOpen.value = !isOpen.value;
    if (isOpen.value) {
        scrollToBottom();
    }
};

const renderMarkdown = (content) => {
    return marked(content);
};

const scrollToBottom = async () => {
    await nextTick();
    if (messageContainer.value) {
        messageContainer.value.scrollTop = messageContainer.value.scrollHeight;
    }
};

const sendMessage = async () => {
    if (!userInput.value.trim() || isLoading.value) return;

    const userMsg = userInput.value;
    messages.value.push({ role: 'user', content: userMsg });
    userInput.value = '';
    isLoading.value = true;

    scrollToBottom();

    try {
        const response = await axios.post('/ai/chat', { message: userMsg });

        if (response.data.status === 'success') {
            messages.value.push({ role: 'assistant', content: response.data.response });
        } else {
            messages.value.push({ role: 'assistant', content: 'Désolé, j\'ai rencontré un problème.' });
        }
    } catch (error) {
        console.error('Error sending message:', error);
        messages.value.push({ role: 'assistant', content: 'Désolé, une erreur est survenue lors de la communication.' });
    } finally {
        isLoading.value = false;
        scrollToBottom();
    }
};
</script>

<style scoped>
.fade-slide-enter-active,
.fade-slide-leave-active {
    transition: all 0.3s ease;
}

.fade-slide-enter-from {
    opacity: 0;
    transform: translateY(20px) scale(0.95);
}

.fade-slide-leave-to {
    opacity: 0;
    transform: translateY(20px) scale(0.95);
}

.markdown-body :deep(p) {
    margin-bottom: 0.5rem;
}

.markdown-body :deep(p:last-child) {
    margin-bottom: 0;
}

.markdown-body :deep(ul),
.markdown-body :deep(ol) {
    margin-left: 1.25rem;
    margin-bottom: 0.5rem;
}

.markdown-body :deep(li) {
    list-style-type: disc;
}
</style>
