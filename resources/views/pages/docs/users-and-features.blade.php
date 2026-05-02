<x-layout.docs title="Users & Features">
    <article class="prose max-w-none">
        <header class="not-prose mb-10 border-b border-gray-100 pb-8">
            <h1 class="font-display mb-4 text-5xl font-semibold leading-tight text-gray-950">Users &amp; features</h1>
            <p class="max-w-2xl text-lg leading-8 text-gray-500">Users define who the product serves. Features group the behaviour those users need.</p>
        </header>

        <h2 id="users">Users</h2>
        <p>Users are roles in the product being specified, not people who log in to Spectacular. Examples include Customer, Staff, Admin, Reviewer, Member, or Anonymous visitor.</p>

        <p>Use summaries for context that affects requirements: permissions, goals, constraints, or assumptions. Keep names short enough to scan in requirement forms and filters.</p>

        <h2 id="features">Features</h2>
        <p>Features are the main sections of functionality. They should be broad enough to contain several requirements and specific enough that stakeholders recognise the area being discussed.</p>

        <h2 id="using-them-together">Using them together</h2>
        <p>Requirements sit inside features and can be assigned to one or more users. This keeps each behaviour connected to both the product area and the audience it affects.</p>

        <pre><code>Feature: Invoicing
Users: Customer, Staff
Requirement: Staff can mark an invoice as paid</code></pre>
    </article>

    <x-docs.next-link href="/docs/requirements">Requirements</x-docs.next-link>
</x-layout.docs>
