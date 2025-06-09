<template>
    <Head title="Create Lesson" />
    
    <div class="min-h-screen bg-gray-100">
        <!-- Navigation -->
        <nav class="bg-white shadow">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex justify-between h-16">
                    <div class="flex items-center">
                        <Link :href="route('admin.dashboard')" class="text-blue-600 hover:text-blue-800 mr-4">
                            ← Back to Dashboard
                        </Link>
                        <h1 class="text-xl font-semibold text-gray-900">Create New Lesson</h1>
                    </div>
                    <div class="flex items-center space-x-4">
                        <span class="text-sm text-gray-700">{{ $page.props.auth.user.name }}</span>
                        <Link :href="route('logout')" method="post" class="text-sm text-red-600 hover:text-red-800">
                            Logout
                        </Link>
                    </div>
                </div>
            </div>
        </nav>

        <!-- Main Content -->
        <div class="max-w-4xl mx-auto py-6 sm:px-6 lg:px-8">
            <div class="bg-white shadow rounded-lg">
                <div class="px-4 py-5 sm:p-6">
                    <form @submit.prevent="submit">
                        <!-- Title Field -->
                        <div class="mb-6">
                            <label for="title" class="block text-sm font-medium text-gray-700 mb-2">
                                Lesson Title *
                            </label>
                            <input
                                id="title"
                                v-model="form.title"
                                type="text"
                                class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                                :class="{ 'border-red-500': form.errors.title }"
                                placeholder="Enter lesson title"
                                required
                            />
                            <div v-if="form.errors.title" class="mt-1 text-sm text-red-600">
                                {{ form.errors.title }}
                            </div>
                        </div>

                        <!-- Subject Field -->
                        <div class="mb-6">
                            <label for="subject" class="block text-sm font-medium text-gray-700 mb-2">
                                Subject *
                            </label>
                            <select
                                id="subject"
                                v-model="form.subject"
                                class="w-full px-3 py-2 text-gray-700 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                                :class="{ 'border-red-500': form.errors.subject }"
                                required
                            >
                                <option value="">Select a subject</option>
                                <option value="Mathematics">Mathematics</option>
                                <option value="Science">Science</option>
                                <option value="English">English</option>
                                <option value="History">History</option>
                                <option value="Geography">Geography</option>
                                <option value="Physics">Physics</option>
                                <option value="Chemistry">Chemistry</option>
                                <option value="Biology">Biology</option>
                                <option value="Computer Science">Computer Science</option>
                                <option value="Art">Art</option>
                                <option value="Music">Music</option>
                                <option value="Physical Education">Physical Education</option>
                            </select>
                            <div v-if="form.errors.subject" class="mt-1 text-sm text-red-600">
                                {{ form.errors.subject }}
                            </div>
                        </div>

                        <!-- Grade Level Field -->
                        <div class="mb-6">
                            <label for="grade_level" class="block text-sm font-medium text-gray-700 mb-2">
                                Grade Level *
                            </label>
                            <select
                                id="grade_level"
                                v-model="form.grade_level"
                                class="w-full px-3 py-2 text-gray-700 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                                :class="{ 'border-red-500': form.errors.grade_level }"
                                required
                            >
                                <option value="">Select grade level</option>
                                <option value="Kindergarten">Kindergarten</option>
                                <option value="Grade 1">Grade 1</option>
                                <option value="Grade 2">Grade 2</option>
                                <option value="Grade 3">Grade 3</option>
                                <option value="Grade 4">Grade 4</option>
                                <option value="Grade 5">Grade 5</option>
                                <option value="Grade 6">Grade 6</option>
                                <option value="Grade 7">Grade 7</option>
                                <option value="Grade 8">Grade 8</option>
                                <option value="Grade 9">Grade 9</option>
                                <option value="Grade 10">Grade 10</option>
                                <option value="Grade 11">Grade 11</option>
                                <option value="Grade 12">Grade 12</option>
                                <option value="College/University">College/University</option>
                            </select>
                            <div v-if="form.errors.grade_level" class="mt-1 text-sm text-red-600">
                                {{ form.errors.grade_level }}
                            </div>
                        </div>

                        <!-- Content Field -->
                        <div class="mb-6">
                            <label for="content" class="block text-sm font-medium text-gray-700 mb-2">
                                Lesson Content *
                            </label>
                            <textarea
                                id="content"
                                v-model="form.content"
                                rows="12"
                                class="w-full px-3 py-2 text-gray-700 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                                :class="{ 'border-red-500': form.errors.content }"
                                placeholder="Enter lesson content here..."
                                required
                            ></textarea>
                            <div v-if="form.errors.content" class="mt-1 text-sm text-red-600">
                                {{ form.errors.content }}
                            </div>
                            <p class="mt-1 text-sm text-gray-500">
                                Write the lesson content that students will read and ask questions about.
                            </p>
                        </div>

                        <!-- Character Counter -->
                        <div class="mb-6">
                            <div class="text-sm text-gray-500 text-right">
                                Characters: {{ form.content.length }}
                            </div>
                        </div>

                        <!-- Form Actions -->
                        <div class="flex items-center justify-between pt-6 border-t border-gray-200">
                            <Link 
                                :href="route('admin.dashboard')" 
                                class="inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
                            >
                                Cancel
                            </Link>
                            <div class="flex space-x-3">
                                <button
                                    type="button"
                                    @click="saveDraft"
                                    class="inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
                                    :disabled="form.processing"
                                >
                                    Save Draft
                                </button>
                                <button
                                    type="submit"
                                    class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 disabled:opacity-50"
                                    :disabled="form.processing"
                                >
                                    <svg v-if="form.processing" class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                    </svg>
                                    {{ form.processing ? 'Creating...' : 'Create Lesson' }}
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Preview Section -->
            <div v-if="form.title || form.content" class="mt-8 bg-white shadow rounded-lg">
                <div class="px-4 py-5 sm:p-6">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Preview</h3>
                    <div class="border-t border-gray-200 pt-4">
                        <div class="mb-4 flex gap-4 text-sm text-gray-600">
                            <span v-if="form.subject" class="bg-blue-100 text-blue-800 px-2 py-1 rounded">{{ form.subject }}</span>
                            <span v-if="form.grade_level" class="bg-green-100 text-green-800 px-2 py-1 rounded">{{ form.grade_level }}</span>
                        </div>
                        <h4 class="text-xl font-semibold text-gray-900 mb-3">{{ form.title || 'Lesson Title' }}</h4>
                        <div class="prose max-w-none text-gray-700 whitespace-pre-wrap">{{ form.content || 'Lesson content will appear here...' }}</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3'
import { onMounted } from 'vue'

const form = useForm({
    title: '',
    subject: '',
    grade_level: '',
    content: ''
})

const submit = () => {
    form.post(route('admin.lessons.store'), {
        onSuccess: () => {
            // Will redirect to lessons index or show page
        },
        onError: (errors) => {
            console.log('Form errors:', errors)
        }
    })
}

const saveDraft = () => {
    // You can implement draft saving functionality here
    alert('Draft saved locally! (This feature can be enhanced to save to server)')
    localStorage.setItem('lesson_draft', JSON.stringify({
        title: form.title,
        subject: form.subject,
        grade_level: form.grade_level,
        content: form.content,
        saved_at: new Date().toISOString()
    }))
}

// Load draft on mount if exists
onMounted(() => {
    const draft = localStorage.getItem('lesson_draft')
    if (draft) {
        const parsedDraft = JSON.parse(draft)
        if (confirm('Found a saved draft. Would you like to load it?')) {
            form.title = parsedDraft.title
            form.subject = parsedDraft.subject || ''
            form.grade_level = parsedDraft.grade_level || ''
            form.content = parsedDraft.content
        }
    }
})
</script>