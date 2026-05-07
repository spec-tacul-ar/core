<x-layout.docs title="Features">
    <article class="prose">
        <h1>Features</h1>

        <p>Features describe what people or systems need to do. They are the bridge between the project summary and detailed requirements. In other places, they may be known as <em>Epics</em>.</p>

        <p>A feature is a large capability area or product value. It should be broad enough to group several related user stories, but not so broad that it becomes a vague container for the whole product.</p>

        <h2 id="fields">Fields</h2>

        <h3>Name</h3>

        <p>A heading for this group of requirements.</p>
        
        <x-docs.examples>
            <x-slot:good>
                <x-docs.example>User Authentication</x-docs.example>
                <x-docs.example>Billing</x-docs.example>
                <x-docs.example>Notifications</x-docs.example>
            </x-slot:good>

            <x-slot:bad>
                <x-docs.example>Stuff</x-docs.example>
                <x-docs.example>Phase 2</x-docs.example>
                <x-docs.example>UI Changes</x-docs.example>
            </x-slot:bad>
        </x-docs.examples>

        <h3>Summary</h3>
        
        <p>A heading for this group of requirements. This is a good place to add information about motivation, high-level implementation details, and anything that's explicitly out of scope.</p>
        
        <x-docs.examples>
            <x-slot:good>
                <x-docs.example>Team collaboration allows project owners, editors, commenters, and viewers to work from the same living specification so decisions, questions, and delivery context do not fragment across chat, tickets, and documents.</x-docs.example>
            </x-slot:good>

            <x-slot:bad>
                <x-docs.example>Lets people invite users.</x-docs.example>
            </x-slot:bad>
        </x-docs.examples>
    </article>

    <x-docs.next-link href="/docs/requirements">Requirements</x-docs.next-link>
</x-layout.docs>
