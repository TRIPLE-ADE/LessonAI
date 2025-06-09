<template>
    <Head title="Browse Lessons" />
    
    <div class="min-h-screen bg-gradient-to-br from-blue-50 to-indigo-100">
        <!-- Navigation -->
        <nav class="bg-white shadow-sm border-b border-gray-200">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex justify-between h-16">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <h1 class="text-xl font-bold text-indigo-600">Learn AI</h1>
                        </div>
                        <div class="ml-10 flex items-baseline space-x-8">
                            <Link :href="route('student.dashboard')" 
                                  class="text-gray-500 hover:text-gray-700 px-3 py-2 text-sm font-medium">
                                Dashboard
                            </Link>
                            <Link :href="route('student.lessons.index')" 
                                  class="text-indigo-600 px-3 py-2 text-sm font-medium border-b-2 border-indigo-600">
                                Browse Lessons
                            </Link>
                            <!-- <Link :href="route('student.chat.history')" 
                                  class="text-gray-500 hover:text-gray-700 px-3 py-2 text-sm font-medium">
                                Chat History
                            </Link> -->
                        </div>
                    </div>
                    <div class="flex items-center space-x-4">
                        <span class="text-sm text-gray-700">Welcome, {{ $page.props.auth.user.name }}</span>
                        <Link :href="route('logout')" method="post" 
                              class="text-sm text-red-600 hover:text-red-800 px-3 py-2 rounded-md hover:bg-red-50">
                            Logout
                        </Link>
                    </div>
                </div>
            </div>
        </nav>

        <!-- Main Content -->
        <div class="max-w-7xl mx-auto py-8 px-4 sm:px-6 lg:px-8">
            <!-- Header -->
            <div class="mb-8">
                <h2 class="text-3xl font-bold text-gray-900 mb-2">Browse Lessons</h2>
                <p class="text-gray-600 text-lg">Discover and explore our collection of learning materials</p>
            </div>

            <!-- Search and Stats -->
            <div class="bg-white rounded-xl shadow-sm p-6 mb-8">
                <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
                    <div class="flex-1 max-w-md">
                        <div class="relative">
                            <input
                                v-model="searchQuery"
                                type="text"
                                placeholder="Search lessons..."
                                class="w-full px-4 py-3 pl-11 pr-4 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                            />
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                                </svg>
                            </div>
                        </div>
                    </div>
                    <div class="flex items-center space-x-6 text-sm text-gray-600">
                        <div class="flex items-center">
                            <div class="w-2 h-2 bg-indigo-500 rounded-full mr-2"></div>
                            <span>{{ filteredLessons.length }} lessons available</span>
                        </div>
                        <div class="flex items-center">
                            <div class="w-2 h-2 bg-green-500 rounded-full mr-2"></div>
                            <span>{{ completedCount }} completed</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Lessons Grid -->
            <div v-if="filteredLessons.length > 0" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <div v-for="lesson in filteredLessons" :key="lesson.id" 
                     class="bg-white rounded-xl shadow-sm hover:shadow-md transition-shadow duration-200 overflow-hidden">
                    <!-- Lesson Card Header -->
                    <div class="p-6 pb-4">
                        <div class="flex items-start justify-between mb-3">
                            <h3 class="text-lg font-semibold text-gray-900 line-clamp-2">{{ lesson.title }}</h3>
                            <div v-if="isCompleted(lesson.id)" class="flex-shrink-0 ml-2">
                                <div class="w-6 h-6 bg-green-500 rounded-full flex items-center justify-center">
                                    <svg class="w-3 h-3 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                    </svg>
                                </div>
                            </div>
                        </div>
                        
                        <p class="text-gray-600 text-sm mb-4 line-clamp-3">
                            {{ truncateText(lesson.content, 120) }}
                        </p>
                        
                        <div class="flex items-center justify-between text-xs text-gray-500 mb-4">
                            <span>{{ lesson.content.length }} characters</span>
                            <span>{{ getQuestionCount(lesson.id) }} questions asked</span>
                        </div>
                    </div>

                    <!-- Progress Bar (if lesson has been started) -->
                    <div v-if="getLessonProgress(lesson.id)" class="px-6 pb-4">
                        <div class="w-full bg-gray-200 rounded-full h-2">
                            <div class="bg-indigo-600 h-2 rounded-full transition-all duration-300" 
                                 :style="{ width: getLessonProgress(lesson.id) + '%' }"></div>
                        </div>
                        <p class="text-xs text-gray-500 mt-1">{{ getLessonProgress(lesson.id) }}% progress</p>
                    </div>

                    <!-- Card Footer -->
                    <div class="px-6 py-4 bg-gray-50 border-t border-gray-100">
                        <div class="flex items-center justify-between">
                            <div class="text-xs text-gray-500">
                                <span>Added {{ formatDate(lesson.created_at) }}</span>
                            </div>
                            <Link :href="route('student.lessons.show', lesson.id)" 
                                  class="inline-flex items-center px-4 py-2 bg-indigo-600 text-white text-sm font-medium rounded-lg hover:bg-indigo-700 transition-colors">
                                <span v-if="isCompleted(lesson.id)">Review</span>
                                <span v-else-if="getLessonProgress(lesson.id)">Continue</span>
                                <span v-else>Start</span>
                                <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                                </svg>
                            </Link>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Empty State -->
            <div v-else class="text-center py-12">
                <div class="max-w-md mx-auto">
                    <svg class="mx-auto h-16 w-16 text-gray-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                              d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.746 0 3.332.477 4.5 1.253v13C19.832 18.477 18.246 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path>
                    </svg>
                    <h3 class="text-lg font-medium text-gray-900 mb-2">
                        {{ searchQuery ? 'No lessons found' : 'No lessons available' }}
                    </h3>
                    <p class="text-gray-600">
                        {{ searchQuery ? 'Try adjusting your search terms or browse all available lessons.' : 'Check back later for new learning content.' }}
                    </p>
                    <button v-if="searchQuery" @click="searchQuery = ''" 
                            class="mt-4 inline-flex items-center px-4 py-2 bg-indigo-600 text-white text-sm font-medium rounded-lg hover:bg-indigo-700">
                        Clear Search
                    </button>
                </div>
            </div>

            <!-- Load More (if pagination is needed) -->
            <div v-if="canLoadMore" class="text-center mt-8">
                <button @click="loadMore" 
                        :disabled="loading"
                        class="inline-flex items-center px-6 py-3 border border-gray-300 text-sm font-medium rounded-lg text-gray-700 bg-white hover:bg-gray-50 disabled:opacity-50">
                    <svg v-if="loading" class="animate-spin -ml-1 mr-2 h-4 w-4" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" 
                              d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    {{ loading ? 'Loading...' : 'Load More Lessons' }}
                </button>
            </div>
        </div>
    </div>
</template>

<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3'
import { ref, computed } from 'vue'

const props = defineProps({
    lessons: {
        type: Array,
        default: () => []
    },
    completedLessons: {
        type: Array,
        default: () => []
    },
    lessonProgress: {
        type: Object,
        default: () => ({})
    },
    questionCounts: {
        type: Object,
        default: () => ({})
    }
})

const searchQuery = ref('')

const filteredLessons = computed(() => {
    if (!searchQuery.value) {
        return props.lessons
    }
    
    const query = searchQuery.value.toLowerCase()
    return props.lessons.filter(lesson => 
        lesson.title.toLowerCase().includes(query) ||
        lesson.content.toLowerCase().includes(query)
    )
})

const completedCount = computed(() => {
    return props.completedLessons.length
})

const formatDate = (date) => {
    return new Date(date).toLocaleDateString('en-US', {
        month: 'short',
        day: 'numeric',
        year: 'numeric'
    })
}

const truncateText = (text, maxLength) => {
    if (!text) return ''
    if (text.length <= maxLength) return text
    return text.substring(0, maxLength) + '...'
}

const isCompleted = (lessonId) => {
    return props.completedLessons.includes(lessonId)
}

const getLessonProgress = (lessonId) => {
    return props.lessonProgress[lessonId] || 0
}

const getQuestionCount = (lessonId) => {
    return props.questionCounts[lessonId] || 0
}
</script>

<style scoped>
.line-clamp-2 {
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}

.line-clamp-3 {
    display: -webkit-box;
    -webkit-line-clamp: 3;
    -webkit-box-orient: vertical;
    overflow: hidden;
}
</style>