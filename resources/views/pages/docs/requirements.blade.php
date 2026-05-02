<x-layout.docs title="Requirements">
    <article class="prose max-w-none">
        <header class="not-prose mb-10 border-b border-gray-100 pb-8">
            <h1 class="font-display mb-4 text-5xl font-semibold leading-tight text-gray-950">Requirements</h1>
            <p class="max-w-2xl text-lg leading-8 text-gray-500">Requirements are the specific behaviours that make a feature implementable and reviewable.</p>
        </header>

        <h2 id="writing-requirements">Writing requirements</h2>
        <p>Write requirement titles as concise behaviour statements. Add detail in the description, including rules, constraints, edge cases, and examples that a reviewer or developer needs to understand.</p>

        <h2 id="tasks">Tasks</h2>
        <p>Tasks break a requirement into implementation steps. Add them when the work is understood well enough to estimate. Task estimates roll up to the requirement, feature, and project summaries.</p>

        <pre><code>Requirement: Customer can reset their password
Tasks:
- Request reset link
- Validate token
- Save new password
- Confirm completion</code></pre>

        <h2 id="unknowns">Unknowns</h2>
        <p>Unknowns capture questions that need a decision. They keep ambiguity visible and attached to the relevant requirement. If an unknown prevents implementation, mark the requirement as blocked and explain why.</p>

        <h2 id="filtering">Filtering</h2>
        <p>The filter sidebar helps readers focus on requirements by status, user, or feature. Use it to review blocked items, unknowns, completed work, estimated work, or requirements for a specific audience.</p>

        <blockquote>Use tasks for known work. Use unknowns for decisions or information the team does not have yet.</blockquote>
    </article>

    <x-docs.next-link href="/docs/roles">Roles</x-docs.next-link>
</x-layout.docs>
