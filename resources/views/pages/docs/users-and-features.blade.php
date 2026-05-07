<x-layout.docs title="Users & Features">
    <article class="prose">
        <h1>Users &amp; features</h1>

        <p>Users and features describe who the software is for and what those people or systems need to do. They are the bridge between the project summary and detailed requirements. In other places, they may be known as <em>Actors</em> and <em>Epics</em>.</p>

        <h2 id="users">Users</h2>

        <p>A user is anyone or anything that interacts with the system. Use users to make the specification less abstract. Software rarely exists in isolation. It exists because someone needs to do something.</p>

        <p>Users are not personas that might emerge during research and discovery. They are purely role-based, behaviour-orientated and not necessarily human. Think of user roles like administrators, automated systems like payment providers, and AI agents.</p>

        <x-docs.tip>If you find yourself adding too many users, you might want to think about creating a new user that encompasses others.</x-docs.tip>

        <h3>Fields</h3>

        <h4>Name</h4>

        <p>A succinct label for this kind of user. Plural works best.</p>
        
        <x-docs.examples>
            <x-slot:good>
                <x-docs.example>Subscribers</x-docs.example>
                <x-docs.example>Anonymous Visitors</x-docs.example>
                <x-docs.example>Back Office Staff</x-docs.example>
                <x-docs.example>AI Agents</x-docs.example>
                <x-docs.example>API Clients</x-docs.example>
            </x-slot:good>

            <x-slot:bad>
                <x-docs.example>User</x-docs.example>
                <x-docs.example>People</x-docs.example>
                <x-docs.example>Sandra from accounts</x-docs.example>
            </x-slot:bad>
        </x-docs.examples>

        <h4>Summary</h4>

        <p>Summarise the duties of this user, how they interact with the system, and their motivations.</p>
        
        <x-docs.examples>
            <x-slot:good>
                <x-docs.example>An anonymous visitor is one who has not authenticated with the system. They will therefore not have access to the members-only area but can still access a limited number of resources.</x-docs.example>
            </x-slot:good>
        </x-docs.examples>

        <h3>Deleting</h3>
        <p>Users cannot be deleted until they have been detached from all requirements.</p>

        <h2 id="features">Features</h2>

        <p>A feature is a large capability area or product value. It should be broad enough to group several related user stories, but not so broad that it becomes a vague container for the whole product.</p>

        <h3>Fields</h3>

        <h4>Name</h4>

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

        <h4>Summary</h4>
        
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
