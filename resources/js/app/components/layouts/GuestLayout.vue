<template>
    <div class="min-h-full flex flex-col">
        <nav class="md:absolute flex items-center h-16 top-0 left-0 mx-4">
            <a href="/" class="text-3xl font-display font-semibold">
                <img src="/images/logo.svg" class="h-8 mt-1 dark:brightness-0 dark:invert" />
            </a>
        </nav>
        <main class="grow flex flex-col justify-center items-center p-4">
            <slot />
        </main>
    </div>
</template>
