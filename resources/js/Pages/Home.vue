<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { useSimpleI18n } from '@/composables/useSimpleI18n';

const { t } = useSimpleI18n();

defineProps<{
    canLogin?: boolean;
    canRegister?: boolean;
}>();
</script>

<template>
    <Head :title="t('home.title')" />
    
    <div class="min-h-screen bg-gradient-to-br from-secondary-50 via-accent-50 to-accent-100 dark:from-neutral-900 dark:via-secondary-900 dark:to-accent-900">
        <!-- Navigation -->
        <nav class="relative px-4 sm:px-6 py-4">
            <div class="max-w-7xl mx-auto flex items-center justify-between gap-2">
                <div class="flex items-center space-x-2 min-w-0 flex-shrink">
                    <div class="w-7 h-7 sm:w-8 sm:h-8 bg-gradient-to-r from-secondary-600 to-accent-600 rounded-lg flex items-center justify-center flex-shrink-0">
                        <svg class="w-4 h-4 sm:w-5 sm:h-5 text-white" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M18 3a1 1 0 00-1.196-.98l-10 2A1 1 0 006 5v9.114A4.369 4.369 0 005 14c-1.657 0-3 .895-3 2s1.343 2 3 2 3-.895 3-2V7.82l8-1.6v5.894A4.369 4.369 0 0015 12c-1.657 0-3 .895-3 2s1.343 2 3 2 3-.895 3-2V3z"/>
                        </svg>
                    </div>
                    <span class="text-base sm:text-xl font-bold text-neutral-900 dark:text-white truncate">
                        {{ t('home.appName') }}
                    </span>
                </div>
                
                <div v-if="canLogin" class="flex items-center gap-2 sm:gap-4 flex-shrink-0">
                    <Link
                        v-if="$page.props.auth.user"
                        :href="route('dashboard')"
                        class="px-3 sm:px-4 py-2 text-sm sm:text-base bg-gradient-to-r from-secondary-600 to-accent-600 text-white rounded-lg hover:from-secondary-700 hover:to-accent-700 transition-all duration-200 font-medium whitespace-nowrap"
                    >
                        {{ t('navigation.dashboard') }}
                    </Link>
                    
                    <template v-else>
                        <Link
                            :href="route('login')"
                            class="px-3 sm:px-4 py-2 text-sm sm:text-base text-neutral-700 dark:text-neutral-300 hover:text-secondary-600 dark:hover:text-secondary-400 transition-colors duration-200 font-medium whitespace-nowrap"
                        >
                            {{ t('navigation.login') }}
                        </Link>
                        <Link
                            v-if="canRegister"
                            :href="route('register')"
                            class="px-3 sm:px-4 py-2 text-sm sm:text-base bg-gradient-to-r from-secondary-600 to-accent-600 text-white rounded-lg hover:from-secondary-700 hover:to-accent-700 transition-all duration-200 font-medium whitespace-nowrap"
                        >
                            {{ t('navigation.register') }}
                        </Link>
                    </template>
                </div>
            </div>
        </nav>

        <!-- Hero Section -->
        <main class="relative">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 py-12 sm:py-20">
                <div class="text-center">
                    <!-- Hero Title -->
                    <h1 class="text-3xl sm:text-4xl md:text-5xl lg:text-6xl font-bold text-neutral-900 dark:text-white mb-4 sm:mb-6">
                        {{ t('home.hero.title') }}
                    </h1>
                    
                    <!-- Hero Subtitle -->
                    <p class="text-base sm:text-lg md:text-xl lg:text-2xl text-neutral-600 dark:text-neutral-300 mb-6 sm:mb-8 max-w-3xl mx-auto leading-relaxed">
                        {{ t('home.hero.subtitle') }}
                    </p>
                    
                    <!-- CTA Buttons -->
                    <div class="flex flex-col sm:flex-row gap-4 justify-center mb-16">
                        <Link
                            v-if="!$page.props.auth.user"
                            :href="route('register')"
                            class="px-8 py-4 bg-gradient-to-r from-secondary-600 to-accent-600 text-white text-lg font-semibold rounded-xl hover:from-secondary-700 hover:to-accent-700 transform hover:scale-105 transition-all duration-200 shadow-lg hover:shadow-xl"
                        >
                            {{ t('home.hero.getStarted') }}
                        </Link>
                        <Link
                            v-else
                            :href="route('dashboard')"
                            class="px-8 py-4 bg-gradient-to-r from-secondary-600 to-accent-600 text-white text-lg font-semibold rounded-xl hover:from-secondary-700 hover:to-accent-700 transform hover:scale-105 transition-all duration-200 shadow-lg hover:shadow-xl"
                        >
                            {{ t('home.hero.goToDashboard') }}
                        </Link>
                        
                        <Link
                            v-if="!$page.props.auth.user"
                            :href="route('login')"
                            class="px-8 py-4 border-2 border-secondary-600 text-secondary-600 dark:text-secondary-400 text-lg font-semibold rounded-xl hover:bg-secondary-600 hover:text-white dark:hover:text-white transition-all duration-200"
                        >
                            {{ t('navigation.login') }}
                        </Link>
                    </div>
                </div>

                <!-- Features Grid -->
                <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-8 mb-20">
                    <!-- Feature 1 -->
                    <div class="bg-white dark:bg-neutral-800 rounded-2xl p-8 shadow-lg hover:shadow-xl transition-shadow duration-200">
                        <div class="w-12 h-12 bg-gradient-to-r from-secondary-500 to-pink-500 rounded-lg flex items-center justify-center mb-6">
                            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        </div>
                        <h3 class="text-xl font-bold text-neutral-900 dark:text-white mb-4">
                            {{ t('home.features.timer.title') }}
                        </h3>
                        <p class="text-neutral-600 dark:text-neutral-300">
                            {{ t('home.features.timer.description') }}
                        </p>
                    </div>

                    <!-- Feature 2 -->
                    <div class="bg-white dark:bg-neutral-800 rounded-2xl p-8 shadow-lg hover:shadow-xl transition-shadow duration-200">
                        <div class="w-12 h-12 bg-gradient-to-r from-accent-500 to-cyan-500 rounded-lg flex items-center justify-center mb-6">
                            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                            </svg>
                        </div>
                        <h3 class="text-xl font-bold text-neutral-900 dark:text-white mb-4">
                            {{ t('home.features.tracking.title') }}
                        </h3>
                        <p class="text-neutral-600 dark:text-neutral-300">
                            {{ t('home.features.tracking.description') }}
                        </p>
                    </div>

                    <!-- Feature 3 -->
                    <div class="bg-white dark:bg-neutral-800 rounded-2xl p-8 shadow-lg hover:shadow-xl transition-shadow duration-200">
                        <div class="w-12 h-12 bg-gradient-to-r from-success-500 to-emerald-500 rounded-lg flex items-center justify-center mb-6">
                            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
                            </svg>
                        </div>
                        <h3 class="text-xl font-bold text-neutral-900 dark:text-white mb-4">
                            {{ t('home.features.notes.title') }}
                        </h3>
                        <p class="text-neutral-600 dark:text-neutral-300">
                            {{ t('home.features.notes.description') }}
                        </p>
                    </div>
                </div>

                <!-- How it Works Section -->
                <div class="text-center mb-20">
                    <h2 class="text-3xl md:text-4xl font-bold text-neutral-900 dark:text-white mb-12">
                        {{ t('home.howItWorks.title') }}
                    </h2>
                    
                    <div class="grid md:grid-cols-4 gap-8">
                        <div class="text-center">
                            <div class="w-16 h-16 bg-gradient-to-r from-secondary-600 to-accent-600 rounded-full flex items-center justify-center mx-auto mb-4">
                                <span class="text-white font-bold text-xl">1</span>
                            </div>
                            <h3 class="text-lg font-semibold text-neutral-900 dark:text-white mb-2">
                                {{ t('home.howItWorks.step1.title') }}
                            </h3>
                            <p class="text-neutral-600 dark:text-neutral-300">
                                {{ t('home.howItWorks.step1.description') }}
                            </p>
                        </div>
                        
                        <div class="text-center">
                            <div class="w-16 h-16 bg-gradient-to-r from-secondary-600 to-accent-600 rounded-full flex items-center justify-center mx-auto mb-4">
                                <span class="text-white font-bold text-xl">2</span>
                            </div>
                            <h3 class="text-lg font-semibold text-neutral-900 dark:text-white mb-2">
                                {{ t('home.howItWorks.step2.title') }}
                            </h3>
                            <p class="text-neutral-600 dark:text-neutral-300">
                                {{ t('home.howItWorks.step2.description') }}
                            </p>
                        </div>
                        
                        <div class="text-center">
                            <div class="w-16 h-16 bg-gradient-to-r from-secondary-600 to-accent-600 rounded-full flex items-center justify-center mx-auto mb-4">
                                <span class="text-white font-bold text-xl">3</span>
                            </div>
                            <h3 class="text-lg font-semibold text-neutral-900 dark:text-white mb-2">
                                {{ t('home.howItWorks.step3.title') }}
                            </h3>
                            <p class="text-neutral-600 dark:text-neutral-300">
                                {{ t('home.howItWorks.step3.description') }}
                            </p>
                        </div>
                        
                        <div class="text-center">
                            <div class="w-16 h-16 bg-gradient-to-r from-secondary-600 to-accent-600 rounded-full flex items-center justify-center mx-auto mb-4">
                                <span class="text-white font-bold text-xl">4</span>
                            </div>
                            <h3 class="text-lg font-semibold text-neutral-900 dark:text-white mb-2">
                                {{ t('home.howItWorks.step4.title') }}
                            </h3>
                            <p class="text-neutral-600 dark:text-neutral-300">
                                {{ t('home.howItWorks.step4.description') }}
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Final CTA -->
                <div class="text-center bg-white dark:bg-neutral-800 rounded-3xl p-12 shadow-xl">
                    <h2 class="text-3xl md:text-4xl font-bold text-neutral-900 dark:text-white mb-6">
                        {{ t('home.cta.title') }}
                    </h2>
                    <p class="text-xl text-neutral-600 dark:text-neutral-300 mb-8 max-w-2xl mx-auto">
                        {{ t('home.cta.description') }}
                    </p>
                    <Link
                        v-if="!$page.props.auth.user"
                        :href="route('register')"
                        class="inline-block px-8 py-4 bg-gradient-to-r from-secondary-600 to-accent-600 text-white text-lg font-semibold rounded-xl hover:from-secondary-700 hover:to-accent-700 transform hover:scale-105 transition-all duration-200 shadow-lg hover:shadow-xl"
                    >
                        {{ t('home.cta.button') }}
                    </Link>
                    <Link
                        v-else
                        :href="route('dashboard')"
                        class="inline-block px-8 py-4 bg-gradient-to-r from-secondary-600 to-accent-600 text-white text-lg font-semibold rounded-xl hover:from-secondary-700 hover:to-accent-700 transform hover:scale-105 transition-all duration-200 shadow-lg hover:shadow-xl"
                    >
                        {{ t('home.cta.button') }}
                    </Link>
                </div>
            </div>
        </main>

        <!-- Footer -->
        <footer class="bg-neutral-900 text-white py-8">
            <div class="max-w-7xl mx-auto px-6 text-center">
                <p class="text-neutral-400">
                    {{ t('home.footer.text') }}
                </p>
            </div>
        </footer>
    </div>
</template>