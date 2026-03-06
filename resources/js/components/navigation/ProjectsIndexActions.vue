<template>
    <RouterLink :to="{ name: 'projects.create' }" class="btn btn-primary">Create project</RouterLink>

    <slot name="after" />
</template>
