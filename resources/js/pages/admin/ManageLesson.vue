<template>
    <Head title="Manage Lessons" />
    
    <div class="min-h-screen bg-gray-100">
        <!-- Navigation -->
        <nav class="bg-white shadow">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex justify-between h-16">
                    <div class="flex items-center">
                        <Link :href="route('admin.dashboard')" class="text-blue-600 hover:text-blue-800 mr-4">
                            ← Back to Dashboard
                        </Link>
                        <h1 class="text-xl font-semibold text-gray-900">Manage Lessons</h1>
                    </div>
                    <div class="flex items-center space-x-4">
                        <Link 
                            :href="route('admin.lessons.create')" 
                            class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700"
                        >
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                            </svg>
                            New Lesson
                        </Link>
                        <span class="text-sm text-gray-700">{{ $page.props.auth.user.name }}</span>
                        <Link :href="route('logout')" method="post" class="text-sm text-red-600 hover:text-red-800">
                            Logout
                        </Link>
                    </div>
                </div>
            </div>
        </nav>

        <!-- Main Content -->
        <div class="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8">
            <!-- Search and Filter -->
            <div class="bg-white shadow rounded-lg mb-6">
                <div class="px-4 py-5 sm:p-6">
                    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between">
                        <div class="flex-1 min-w-0">
                            <div class="relative">
                                <input
                                    v-model="searchQuery"
                                    type="text"
                                    placeholder="Search lessons..."
                                    class="w-full px-4 py-2 pl-10 pr-4 border text-gray-700 border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                />
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                                    </svg>
                                </div>
                            </div>
                        </div>
                        <div class="mt-4 sm:mt-0 sm:ml-4">
                            <span class="text-sm text-gray-700">
                                {{ filteredLessons.length }} lesson{{ filteredLessons.length !== 1 ? 's' : '' }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Lessons List -->
            <div class="bg-white shadow rounded-lg">
                <div v-if="filteredLessons.length > 0">
                    <ul class="divide-y divide-gray-200">
                        <li v-for="lesson in filteredLessons" :key="lesson.id" class="px-4 py-5 sm:px-6 hover:bg-gray-50">
                            <div class="flex items-center justify-between">
                                <div class="flex-1 min-w-0">
                                    <div class="flex items-center justify-between">
                                        <h3 class="text-lg font-medium text-gray-900 truncate">
                                            {{ lesson.title }}
                                        </h3>
                                        <div class="flex-shrink-0 ml-4">
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                                {{ lesson.questions_count || 0 }} questions
                                            </span>
                                        </div>
                                    </div>
                                    <div class="mt-2">
                                        <p class="text-sm text-gray-600 line-clamp-2">
                                            {{ truncateContent(lesson.content, 150) }}
                                        </p>
                                    </div>
                                    <div class="mt-2 flex items-center text-xs text-gray-500">
                                        <span>Created {{ formatDate(lesson.created_at) }}</span>
                                        <span class="mx-2">•</span>
                                        <span>{{ lesson.content.length }} characters</span>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Actions -->
                            <div class="mt-4 flex items-center space-x-4">
                                <Link 
                                    :href="route('admin.lessons.show', lesson.id)"
                                    class="text-sm text-blue-600 hover:text-blue-800"
                                >
                                    View
                                </Link>
                                <Link 
                                    :href="route('admin.lessons.edit', lesson.id)"
                                    class="text-sm text-yellow-600 hover:text-yellow-800"
                                >
                                    Edit
                                </Link>
                                <Link 
                                    :href="route('admin.lessons.questions', lesson.id)"
                                    class="text-sm text-green-600 hover:text-green-800"
                                >
                                    Q&A ({{ lesson.questions_count || 0 }})
                                </Link>
                                <button
                                    @click="confirmDelete(lesson)"
                                    class="text-sm text-red-600 hover:text-red-800"
                                >
                                    Delete
                                </button>
                            </div>
                        </li>
                    </ul>
                </div>
                
                <!-- Empty State -->
                <div v-else class="text-center py-12">
                    <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.746 0 3.332.477 4.5 1.253v13C19.832 18.477 18.246 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path>
                    </svg>
                    <h3 class="mt-2 text-sm font-medium text-gray-900">
                        {{ searchQuery ? 'No lessons found' : 'No lessons created yet' }}
                    </h3>
                    <p class="mt-1 text-sm text-gray-500">
                        {{ searchQuery ? 'Try adjusting your search terms.' : 'Get started by creating your first lesson.' }}
                    </p>
                    <div v-if="!searchQuery" class="mt-6">
                        <Link 
                            :href="route('admin.lessons.create')" 
                            class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700"
                        >
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                            </svg>
                            Create Lesson
                        </Link>
                    </div>
                </div>
            </div>
        </div>

        <!-- Delete Confirmation Modal -->
        <div v-if="showDeleteModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
            <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
                <div class="mt-3 text-center">
                    <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-red-100">
                        <svg class="h-6 w-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.732 15.5c-.77.833.192 2.5 1.732 2.5z"></path>
                        </svg>
                    </div>
                    <h3 class="text-lg leading-6 font-medium text-gray-900 mt-4">Delete Lesson</h3>
                    <div class="mt-2 px-7 py-3">
                        <p class="text-sm text-gray-500">
                            Are you sure you want to delete "<strong>{{ lessonToDelete?.title }}</strong>"? 
                            This action cannot be undone and will also delete all associated questions and answers.
                        </p>
                    </div>
                    <div class="flex justify-center gap-4 mt-4">
                        <button
                            @click="showDeleteModal = false"
                            class="px-4 py-2 bg-gray-300 text-gray-800 text-base font-medium rounded-md hover:bg-gray-400 focus:outline-none focus:ring-2 focus:ring-gray-300"
                        >
                            Cancel
                        </button>
                        <button
                            @click="deleteLesson"
                            :disabled="deleting"
                            class="px-4 py-2 bg-red-600 text-white text-base font-medium rounded-md hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500 disabled:opacity-50"
                        >
                            <span v-if="deleting">Deleting...</span>
                            <span v-else>Delete</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3'
import { ref, computed, watch, onUnmounted } from 'vue'

let modalListener = null

const props = defineProps({
    lessons: {
        type: Array,
        default: () => []
    }
})

// Reactive data
const searchQuery = ref('')
const showDeleteModal = ref(false)
const lessonToDelete = ref(null)
const deleting = ref(false)

// Computed properties
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

// Methods
const formatDate = (date) => {
    return new Date(date).toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'short',
        day: 'numeric'
    })
}

const truncateContent = (content, maxLength) => {
    if (!content) return ''
    if (content.length <= maxLength) return content
    return content.substring(0, maxLength) + '...'
}

const confirmDelete = (lesson) => {
    lessonToDelete.value = lesson
    showDeleteModal.value = true
}

const deleteLesson = () => {
    if (!lessonToDelete.value) return
    
    deleting.value = true
    
    router.delete(route('admin.lessons.destroy', lessonToDelete.value.id), {
        onSuccess: () => {
            showDeleteModal.value = false
            lessonToDelete.value = null
            deleting.value = false
        },
        onError: (errors) => {
            console.error('Failed to delete lesson:', errors)
            deleting.value = false
            alert('Failed to delete lesson. Please try again.')
        }
    })
}

// Close modal when clicking outside
const closeModalOnOutsideClick = (event) => {
    if (event.target.classList.contains('bg-opacity-50')) {
        showDeleteModal.value = false
    }
}

watch(showDeleteModal, (val) => {
    if (val) {
        modalListener = (e) => closeModalOnOutsideClick(e)
        document.addEventListener('click', modalListener)
    } else if (modalListener) {
        document.removeEventListener('click', modalListener)
        modalListener = null
    }
})

onUnmounted(() => {
    if (modalListener) {
        document.removeEventListener('click', modalListener)
    }
})
</script>

<style scoped>
.line-clamp-2 {
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}
</style>