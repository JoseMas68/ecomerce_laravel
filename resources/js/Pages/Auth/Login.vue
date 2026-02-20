<script setup>
import AuthenticatedLayout from '@/Components/Layout/AuthenticatedLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';
import { route } from '@/Composables/useRoutes';

const form = useForm({
    email: '',
    password: '',
    remember: false,
});

const submit = () => {
    form.post(route('login.post'), {
        onFinish: () => form.reset('password'),
    });
};

const showPassword = ref(false);
</script>

<template>
    <AuthenticatedLayout>
        <Head title="Iniciar Sesión" />

        <div class="min-h-[calc(100vh-200px)] flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8 bg-white">
            <div class="max-w-md w-full space-y-8">
                <!-- Header -->
                <div>
                    <h2 class="mt-6 text-center text-3xl font-extrabold text-gray-900">
                        Iniciar Sesión
                    </h2>
                    <p class="mt-2 text-center text-sm text-gray-600">
                        O
                        <a :href="route('register')" class="font-medium text-emerald-500 hover:text-emerald-600">
                            crea una cuenta nueva
                        </a>
                    </p>
                </div>

                <!-- Form -->
                <form class="mt-8 space-y-6" @submit.prevent="submit">
                    <div v-if="form.errors.email" class="rounded-md bg-red-50 p-4">
                        <p class="text-sm text-red-800">{{ form.errors.email }}</p>
                    </div>

                    <div class="rounded-md shadow-sm space-y-4">
                        <!-- Email -->
                        <div>
                            <label for="email" class="block text-sm font-medium text-gray-700 mb-1">
                                Correo Electrónico
                            </label>
                            <input
                                id="email"
                                v-model="form.email"
                                type="email"
                                autocomplete="email"
                                required
                                class="appearance-none relative block w-full px-3 py-2 border border-gray-300 placeholder-gray-500 text-gray-900 rounded-md focus:outline-none focus:ring-emerald-500 focus:border-emerald-500 focus:z-10 sm:text-sm"
                                placeholder="tu@ejemplo.com"
                                :class="{ 'border-red-300': form.errors.email }"
                            />
                        </div>

                        <!-- Password -->
                        <div>
                            <label for="password" class="block text-sm font-medium text-gray-700 mb-1">
                                Contraseña
                            </label>
                            <div class="relative">
                                <input
                                    id="password"
                                    v-model="form.password"
                                    :type="showPassword ? 'text' : 'password'"
                                    autocomplete="current-password"
                                    required
                                    class="appearance-none relative block w-full px-3 py-2 border border-gray-300 placeholder-gray-500 text-gray-900 rounded-md focus:outline-none focus:ring-emerald-500 focus:border-emerald-500 focus:z-10 sm:text-sm pr-10"
                                    placeholder="••••••••"
                                    :class="{ 'border-red-300': form.errors.password }"
                                />
                                <button
                                    type="button"
                                    @click="showPassword = !showPassword"
                                    class="absolute inset-y-0 right-0 pr-3 flex items-center"
                                >
                                    <svg v-if="!showPassword" class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                    </svg>
                                    <svg v-else class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21" />
                                    </svg>
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Remember me & Forgot password -->
                    <div class="flex items-center justify-between">
                        <div class="flex items-center">
                            <input
                                id="remember-me"
                                v-model="form.remember"
                                type="checkbox"
                                class="h-4 w-4 text-emerald-600 focus:ring-emerald-500 border-gray-300 rounded"
                            />
                            <label for="remember-me" class="ml-2 block text-sm text-gray-900">
                                Recordarme
                            </label>
                        </div>

                        <div class="text-sm">
                            <a :href="route('password.request')" class="font-medium text-emerald-600 hover:text-emerald-500">
                                ¿Olvidaste tu contraseña?
                            </a>
                        </div>
                    </div>

                    <!-- Submit button -->
                    <div>
                        <button
                            type="submit"
                            :disabled="form.processing"
                            class="group relative w-full flex justify-center py-3 px-4 border border-transparent text-sm font-medium rounded-md text-white bg-emerald-500 hover:bg-emerald-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-emerald-500 disabled:opacity-50 disabled:cursor-not-allowed"
                        >
                            <span v-if="!form.processing">Iniciar Sesión</span>
                            <span v-else>
                                <svg class="animate-spin h-5 w-5 mx-auto" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                            </span>
                        </button>
                    </div>
                </form>

                <!-- Demo credentials -->
                <div class="mt-6 bg-blue-50 rounded-lg p-4">
                    <p class="text-sm text-blue-800 font-medium mb-2">Credenciales de Demo:</p>
                    <div class="space-y-1 text-xs text-blue-700">
                        <p><strong>Admin:</strong> admin@ecommerce.com / password123</p>
                        <p><strong>Cliente:</strong> cliente1@ecommerce.com / password123</p>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
