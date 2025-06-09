<template>
    <Head :title="lesson.title" />

    <div class="min-h-screen bg-gradient-to-br from-blue-50 to-indigo-100">
        <!-- Navigation -->
        <nav class="border-b border-gray-200 bg-white shadow-sm">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <div class="flex h-16 justify-between">
                    <div class="flex items-center">
                        <Link :href="route('student.lessons.index')" class="mr-4 flex items-center text-indigo-600 hover:text-indigo-800">
                            <svg class="mr-1 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                            </svg>
                            Back to Lessons
                        </Link>
                        <div class="flex-shrink-0">
                            <h1 class="text-xl font-bold text-indigo-600">Learn AI</h1>
                        </div>
                    </div>
                    <div class="flex items-center space-x-4">
                        <span class="text-sm text-gray-700">{{ $page.props.auth.user.name }}</span>
                        <Link
                            :href="route('logout')"
                            method="post"
                            class="rounded-md px-3 py-2 text-sm text-red-600 hover:bg-red-50 hover:text-red-800"
                        >
                            Logout
                        </Link>
                    </div>
                </div>
            </div>
        </nav>

        <!-- Main Content -->
        <div class="mx-auto max-w-7xl px-4 py-6 sm:px-6 lg:px-8">
            <div class="grid h-[calc(100vh-200px)] grid-cols-1 gap-8 lg:grid-cols-2">
                <!-- Lesson Content Panel -->
                <div class="flex flex-col overflow-hidden rounded-xl bg-white shadow-sm">
                    <!-- Lesson Header -->
                    <div class="border-b border-gray-200 p-6">
                        <div class="flex items-start justify-between">
                            <div class="flex-1">
                                <h2 class="mb-2 text-2xl font-bold text-gray-900">{{ lesson.title }}</h2>
                                <div class="flex items-center space-x-4 text-sm text-gray-500">
                                    <span>{{ lesson.content.length }} characters</span>
                                    <span>•</span>
                                    <span>{{ questionsCount }} questions asked</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Lesson Content -->
                    <div class="flex-1 overflow-y-auto p-6">
                        <div class="prose max-w-none text-gray-700">
                            <div class="leading-relaxed whitespace-pre-wrap text-gray-800">{{ lesson.content }}</div>
                        </div>
                    </div>

                    <!-- Lesson Footer -->
                    <div class="border-t border-gray-200 bg-gray-50 p-4">
                        <div class="flex items-center justify-between text-sm text-gray-600">
                            <span>Last updated {{ formatDate(lesson.updated_at) }}</span>
                            <div class="flex items-center space-x-4">
                                <button
                                    @click="markAsComplete"
                                    v-if="!isCompleted"
                                    class="inline-flex items-center rounded-md bg-green-600 px-3 py-1 text-sm font-medium text-white hover:bg-green-700"
                                >
                                    <svg class="mr-1 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                    </svg>
                                    Mark Complete
                                </button>
                                <div v-else class="inline-flex items-center rounded-md bg-green-100 px-3 py-1 text-sm font-medium text-green-800">
                                    <svg class="mr-1 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                    </svg>
                                    Completed
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Chat Interface Panel -->
                <div class="flex flex-col overflow-hidden rounded-xl bg-white shadow-sm">
                    <!-- Chat Header -->
                    <div class="border-b border-gray-200 bg-gradient-to-r from-indigo-500 to-purple-600 p-4 text-white">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center">
                                <div class="bg-opacity-20 mr-3 flex h-8 w-8 items-center justify-center rounded-full bg-white">
                                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                            stroke-width="2"
                                            d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"
                                        ></path>
                                    </svg>
                                </div>
                                <div>
                                    <h3 class="font-semibold">AI Assistant</h3>
                                    <p class="text-sm text-indigo-100">Ask questions about this lesson</p>
                                </div>
                            </div>
                            <button @click="clearChat" class="hover:bg-opacity-10 rounded-lg p-2 transition-colors hover:bg-white" title="Clear chat">
                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="2"
                                        d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"
                                    ></path>
                                </svg>
                            </button>
                        </div>
                    </div>

                    <!-- Chat Messages -->
                    <div ref="chatMessages" class="flex-1 space-y-4 overflow-y-auto p-4">
                        <!-- Welcome Message -->
                        <div v-if="messages.length === 0" class="py-8 text-center">
                            <div class="mx-auto mb-4 flex h-16 w-16 items-center justify-center rounded-full bg-indigo-100">
                                <svg class="h-8 w-8 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="2"
                                        d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"
                                    ></path>
                                </svg>
                            </div>
                            <h4 class="mb-2 text-lg font-medium text-gray-900">Ready to help! 🤖</h4>
                            <p class="mx-auto max-w-xs text-sm text-gray-600">
                                Ask me anything about "{{ lesson.title }}" and I'll help explain the concepts.
                            </p>
                        </div>

                        <!-- Chat Messages -->
                        <div
                            v-for="message in messages"
                            :key="message.id"
                            class="flex"
                            :class="{ 'justify-end': message.type === 'user', 'justify-start': message.type === 'assistant' }"
                        >
                            <div
                                class="max-w-xs rounded-lg px-4 py-3 lg:max-w-md"
                                :class="message.type === 'user' ? 'bg-indigo-600 text-white' : 'bg-gray-50 text-gray-900 border border-gray-200'"
                            >
                                <div class="flex items-start space-x-3" v-if="message.type === 'assistant'">
                                    <div class="mt-1 flex h-7 w-7 flex-shrink-0 items-center justify-center rounded-full bg-indigo-100">
                                        <svg class="h-4 w-4 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path
                                                stroke-linecap="round"
                                                stroke-linejoin="round"
                                                stroke-width="2"
                                                d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"
                                            ></path>
                                        </svg>
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <!-- Professional AI Response Formatting -->
                                        <div class="ai-response-content" v-html="formatAIResponse(message.content)"></div>
                                        <p class="mt-2 text-xs text-gray-500">{{ formatTime(message.created_at) }}</p>
                                    </div>
                                </div>
                                <div v-else>
                                    <p class="text-sm whitespace-pre-wrap leading-relaxed">{{ message.content }}</p>
                                    <p class="mt-1 text-right text-xs text-indigo-200">{{ formatTime(message.created_at) }}</p>
                                </div>
                            </div>
                        </div>

                        <!-- Loading indicator -->
                        <div v-if="isLoading" class="flex justify-start">
                            <div class="rounded-lg bg-gray-50 border border-gray-200 px-4 py-3">
                                <div class="flex items-center space-x-3">
                                    <div class="flex h-7 w-7 items-center justify-center rounded-full bg-indigo-100">
                                        <svg class="h-4 w-4 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path
                                                stroke-linecap="round"
                                                stroke-linejoin="round"
                                                stroke-width="2"
                                                d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"
                                            ></path>
                                        </svg>
                                    </div>
                                    <div class="flex items-center space-x-1">
                                        <span class="text-sm text-gray-600 mr-2">AI is thinking</span>
                                        <div class="flex space-x-1">
                                            <div class="h-2 w-2 animate-bounce rounded-full bg-indigo-400"></div>
                                            <div class="h-2 w-2 animate-bounce rounded-full bg-indigo-400" style="animation-delay: 0.1s"></div>
                                            <div class="h-2 w-2 animate-bounce rounded-full bg-indigo-400" style="animation-delay: 0.2s"></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Chat Input -->
                    <div class="border-t border-gray-200 p-4">
                        <form @submit.prevent="sendMessage" class="flex space-x-2">
                            <input
                                v-model="newMessage"
                                type="text"
                                placeholder="Ask a question about this lesson..."
                                class="flex-1 rounded-lg border border-gray-300 px-4 py-2 text-gray-800 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500 focus:outline-none"
                                :disabled="isLoading"
                            />
                            <button
                                type="submit"
                                :disabled="!newMessage.trim() || isLoading"
                                class="rounded-lg bg-indigo-600 px-4 py-2 font-medium text-white hover:bg-indigo-700 focus:ring-2 focus:ring-indigo-500 focus:outline-none disabled:cursor-not-allowed disabled:opacity-50"
                            >
                                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"></path>
                                </svg>
                            </button>
                        </form>
                        <p class="mt-2 text-xs text-gray-500">
                            Press Enter to send • {{ messages.filter((m) => m.type === 'user').length }} questions asked
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import axios from 'axios';
import { nextTick, onMounted, reactive, ref } from 'vue';

const props = defineProps({
    lesson: {
        type: Object,
        required: true,
    },
    messages: {
        type: Array,
        default: () => [],
    },
    questionsCount: {
        type: Number,
        default: 0,
    },
    isCompleted: {
        type: Boolean,
        default: false,
    },
    isBookmarked: {
        type: Boolean,
        default: false,
    },
});

const newMessage = ref('');
const isLoading = ref(false);
const chatMessages = ref(null);

// Make messages reactive so they update
const state = reactive({
    messages: props.messages || [],
    questionsCount: props.questionsCount || 0,
    isBookmarked: props.isBookmarked || false,
});

const scrollToBottom = () => {
    nextTick(() => {
        if (chatMessages.value) {
            chatMessages.value.scrollTop = chatMessages.value.scrollHeight;
        }
    });
};

// Professional AI Response Formatting Function
const formatAIResponse = (content) => {
    if (!content) return '';
    
    let formatted = content
        // Convert **bold** to <strong>
        .replace(/\*\*([^*]+)\*\*/g, '<strong class="font-semibold text-gray-900">$1</strong>')
        // Convert *italic* to <em>
        .replace(/\*([^*]+)\*/g, '<em class="italic text-gray-800">$1</em>')
        // Convert bullet points (starting with *)
        .replace(/^\* (.+)$/gm, '<div class="flex items-start space-x-2 my-2"><div class="flex-shrink-0 w-1.5 h-1.5 bg-indigo-500 rounded-full mt-2"></div><div class="text-sm text-gray-700 leading-relaxed">$1</div></div>')
        // Convert numbered lists
        .replace(/^(\d+)\. (.+)$/gm, '<div class="flex items-start space-x-2 my-2"><div class="flex-shrink-0 w-5 h-5 bg-indigo-100 text-indigo-700 rounded-full text-xs font-medium flex items-center justify-center mt-0.5">$1</div><div class="text-sm text-gray-700 leading-relaxed">$2</div></div>')
        // Convert line breaks to proper spacing
        .replace(/\n\n/g, '</div><div class="my-3"></div><div class="text-sm text-gray-700 leading-relaxed">')
        .replace(/\n/g, '<br>')
        // Wrap the content in a container
        .replace(/^/, '<div class="text-sm text-gray-700 leading-relaxed">')
        .replace(/$/, '</div>');

    // Handle special sections (like key points)
    formatted = formatted.replace(
        /Key Points?:?\s*/gi, 
        '<div class="mt-3 mb-2"><div class="inline-flex items-center px-2 py-1 rounded-md bg-indigo-100 text-indigo-800 text-xs font-medium mb-2"><svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>Key Points</div></div>'
    );

    // Handle "In summary" or "To summarize" sections
    formatted = formatted.replace(
        /(In summary|To summarize|Summary):?\s*/gi, 
        '<div class="mt-3 mb-2"><div class="inline-flex items-center px-2 py-1 rounded-md bg-green-100 text-green-800 text-xs font-medium mb-2"><svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path></svg>Summary</div></div>'
    );

    return formatted;
};

const sendMessage = async () => {
    if (!newMessage.value.trim() || isLoading.value) return;

    const messageContent = newMessage.value.trim();

    // Add user message immediately to UI for better UX
    const tempUserMessage = {
        id: 'temp_user_' + Date.now(),
        type: 'user',
        content: messageContent,
        created_at: new Date().toISOString(),
    };

    state.messages.push(tempUserMessage);
    newMessage.value = '';
    isLoading.value = true;
    scrollToBottom();

    try {
        const response = await axios.post(route('student.lessons.ask', props.lesson.id), {
            question: messageContent,
        });

        if (response.data.success) {
            // Remove the temporary user message
            const tempIndex = state.messages.findIndex((m) => m.id === tempUserMessage.id);
            if (tempIndex !== -1) {
                state.messages.splice(tempIndex, 1);
            }

            // Add the actual user message and AI response
            state.messages.push(response.data.newUserMessage);

            // Add AI response with a slight delay for natural chat feel
            setTimeout(() => {
                state.messages.push(response.data.newAiMessage);
                scrollToBottom();
            }, 200);

            // Update question count
            state.questionsCount = response.data.questionsCount;
            scrollToBottom();
        } else {
            // Remove the temporary user message on error
            const tempIndex = state.messages.findIndex((m) => m.id === tempUserMessage.id);
            if (tempIndex !== -1) {
                state.messages.splice(tempIndex, 1);
            }
            alert(response.data.error || 'Failed to send message. Please try again.');
        }
    } catch (error) {
        console.error('Error sending message:', error);
        // Remove the temporary user message on error
        const tempIndex = state.messages.findIndex((m) => m.id === tempUserMessage.id);
        if (tempIndex !== -1) {
            state.messages.splice(tempIndex, 1);
        }
        alert('Failed to send message. Please try again.');
    } finally {
        isLoading.value = false;
    }
};

const clearChat = () => {
    if (confirm('Are you sure you want to clear the chat history for this lesson?')) {
        router.delete(route('student.lessons.clear-chat', props.lesson.id), {
            preserveState: true,
            onSuccess: () => {
                state.messages = [];
                state.questionsCount = 0;
            },
            onError: () => {
                alert('Failed to clear chat. Please try again.');
            },
        });
    }
};

const markAsComplete = () => {
    router.post(
        route('student.lessons.complete', props.lesson.id),
        {},
        {
            preserveState: true,
            onSuccess: () => {
                // Lesson completion status will update
            },
            onError: () => {
                alert('Failed to mark lesson as complete. Please try again.');
            },
        },
    );
};

const formatDate = (date) => {
    return new Date(date).toLocaleDateString('en-US', {
        month: 'short',
        day: 'numeric',
        year: 'numeric',
    });
};

const formatTime = (timestamp) => {
    return new Date(timestamp).toLocaleTimeString('en-US', {
        hour: '2-digit',
        minute: '2-digit',
    });
};

// Scroll to bottom when component mounts if there are messages
onMounted(() => {
    if (state.messages.length > 0) {
        scrollToBottom();
    }
});
</script>

<style scoped>
.prose {
    color: inherit;
}

.prose h1,
.prose h2,
.prose h3,
.prose h4,
.prose h5,
.prose h6 {
    color: inherit;
    margin-top: 1.5em;
    margin-bottom: 0.5em;
    font-weight: 600;
}

.prose p {
    margin-top: 1em;
    margin-bottom: 1em;
}

.prose ul,
.prose ol {
    margin-top: 1em;
    margin-bottom: 1em;
    padding-left: 1.5em;
}

.prose li {
    margin-top: 0.25em;
    margin-bottom: 0.25em;
}

.prose strong {
    font-weight: 600;
}

.prose em {
    font-style: italic;
}

.prose code {
    background-color: #f3f4f6;
    padding: 0.125rem 0.25rem;
    border-radius: 0.25rem;
    font-size: 0.875em;
    font-family: ui-monospace, SFMono-Regular, 'SF Mono', Monaco, Consolas, 'Liberation Mono', 'Courier New', monospace;
}

.prose pre {
    background-color: #f3f4f6;
    padding: 1rem;
    border-radius: 0.5rem;
    overflow-x: auto;
    margin-top: 1em;
    margin-bottom: 1em;
}

.prose blockquote {
    border-left: 4px solid #e5e7eb;
    padding-left: 1rem;
    font-style: italic;
    margin-top: 1em;
    margin-bottom: 1em;
}

/* Professional AI Response Styling */
.ai-response-content {
    line-height: 1.6;
}

.ai-response-content strong {
    font-weight: 600;
    color: #1f2937;
}

.ai-response-content em {
    font-style: italic;
    color: #374151;
}

/* Custom scrollbar for chat messages */
.overflow-y-auto::-webkit-scrollbar {
    width: 6px;
}

.overflow-y-auto::-webkit-scrollbar-track {
    background: #f1f1f1;
    border-radius: 3px;
}

.overflow-y-auto::-webkit-scrollbar-thumb {
    background: #c1c1c1;
    border-radius: 3px;
}

.overflow-y-auto::-webkit-scrollbar-thumb:hover {
    background: #a8a8a8;
}

/* Animation for bounce effect */
@keyframes bounce {
    0%,
    80%,
    100% {
        transform: translateY(0);
    }

    40% {
        transform: translateY(-6px);
    }
}

.animate-bounce {
    animation: bounce 1s infinite;
}

/* Enhanced message bubble styling */
.ai-response-content div {
    margin-bottom: 0.5rem;
}

.ai-response-content div:last-child {
    margin-bottom: 0;
}

/* Hover effects for interactive elements */
.ai-response-content strong:hover {
    color: #4f46e5;
    transition: color 0.2s ease;
}
</style>