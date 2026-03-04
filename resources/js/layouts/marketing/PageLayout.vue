<template>
    <div class="relative min-h-svh overflow-hidden bg-background text-foreground">
        <div
            class="pointer-events-none absolute inset-x-0 top-0 h-[30rem] bg-[radial-gradient(circle_at_top,_hsl(221_83%_53%_/_0.16)_0%,_transparent_70%)]"
        />
        <div
            class="pointer-events-none absolute inset-x-0 bottom-0 h-72 bg-[radial-gradient(circle_at_bottom,_var(--color-muted)_0%,_transparent_70%)]"
        />

        <div class="relative flex min-h-svh flex-col">
            <MarketingHeader />

            <main class="mx-auto flex w-full max-w-7xl flex-1 flex-col px-4 py-8 sm:px-6 sm:py-10 lg:px-8 lg:py-16">
                <slot />
            </main>

            <MarketingFooter />
        </div>
    </div>
</template>
