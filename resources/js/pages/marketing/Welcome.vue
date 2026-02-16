<script setup lang="ts">
    const appName = import.meta.env.VITE_APP_NAME || 'Laravel Starter Kit'
</script>

<template>
    <Head title="Welcome" />

    <MarketingPageLayout>
        <header class="flex items-center justify-between gap-4 pb-10">
            <Link :href="route('home')" class="inline-flex items-center gap-3 text-sm font-semibold tracking-wide text-foreground">
                <AppLogoIcon class="size-7 fill-current text-foreground" />
                <span>{{ appName }}</span>
            </Link>

            <nav class="flex items-center gap-2">
                <UiButton v-if="$page.props.auth.user" as-child>
                    <Link :href="route('dashboard')">Dashboard</Link>
                </UiButton>
                <template v-else>
                    <UiButton variant="ghost" as-child>
                        <Link :href="route('login')">Log in</Link>
                    </UiButton>
                    <UiButton as-child>
                        <Link :href="route('register')">Get started</Link>
                    </UiButton>
                </template>
            </nav>
        </header>

        <main class="grid gap-6 lg:grid-cols-[minmax(0,1.2fr)_minmax(0,1fr)] lg:items-center">
            <section class="space-y-6">
                <span class="inline-flex items-center rounded-full border border-border bg-card px-3 py-1 text-xs font-medium text-muted-foreground">
                    Public marketing page
                </span>
                <div class="space-y-4">
                    <h1 class="max-w-2xl text-4xl font-semibold tracking-tight text-foreground sm:text-5xl">
                        Build secure Laravel and Vue products with a consistent design system.
                    </h1>
                    <p class="max-w-2xl text-base text-muted-foreground sm:text-lg">
                        This starter kit is optimized for typed contracts, robust security defaults, and reusable UI patterns so teams can ship
                        faster without duplicate code.
                    </p>
                </div>
                <div class="flex flex-wrap items-center gap-3">
                    <UiButton class="h-11 px-6" as-child>
                        <Link :href="$page.props.auth.user ? route('dashboard') : route('register')">
                            {{ $page.props.auth.user ? 'Open dashboard' : 'Create account' }}
                        </Link>
                    </UiButton>
                    <UiButton variant="outline" class="h-11 px-6" as-child>
                        <Link :href="route('login')">Explore auth flow</Link>
                    </UiButton>
                </div>
            </section>

            <section class="grid gap-4 sm:grid-cols-2">
                <UiCard class="border-border/80 bg-card/70 backdrop-blur-sm">
                    <UiCardHeader class="pb-2">
                        <UiCardTitle class="text-base">Type-safe by default</UiCardTitle>
                    </UiCardHeader>
                    <UiCardContent class="pt-0 text-sm text-muted-foreground">
                        Shared DTOs and generated TypeScript keep backend and frontend contracts aligned.
                    </UiCardContent>
                </UiCard>

                <UiCard class="border-border/80 bg-card/70 backdrop-blur-sm">
                    <UiCardHeader class="pb-2">
                        <UiCardTitle class="text-base">Secure foundations</UiCardTitle>
                    </UiCardHeader>
                    <UiCardContent class="pt-0 text-sm text-muted-foreground">
                        Policy-based authorization, hardened headers, and validated form requests are built in.
                    </UiCardContent>
                </UiCard>

                <UiCard class="border-border/80 bg-card/70 backdrop-blur-sm sm:col-span-2">
                    <UiCardHeader class="pb-2">
                        <UiCardTitle class="text-base">Reusable UI architecture</UiCardTitle>
                    </UiCardHeader>
                    <UiCardContent class="pt-0 text-sm text-muted-foreground">
                        Namespaced pages, layouts, and components keep marketing and dashboard domains clearly separated.
                    </UiCardContent>
                </UiCard>
            </section>
        </main>
    </MarketingPageLayout>
</template>
