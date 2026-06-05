<x-layout.docs title="Users">
    <article class="prose">
        <h1>Users</h1>

        <p>Users describe who the software is for. In other places, they may be known as <em>Actors</em>.</p>

        <p>A user is anyone or anything that interacts with the system. Use users to make the specification less abstract. Software rarely exists in isolation. It exists because someone needs to do something.</p>

        <p>Users are not personas that might emerge during research and discovery. They are purely role-based, behaviour-orientated and not necessarily human. Think of user roles like administrators, automated systems like payment providers, and AI agents.</p>

        <x-docs.tip>If you find yourself adding too many users, you might want to think about creating a new user that encompasses others.</x-docs.tip>

        <h2 id="fields">Fields</h2>

        <h3>Name</h3>

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
                <x-docs.example>Registered users with more than two roles</x-docs.example>
                <x-docs.example>Sandra from accounts</x-docs.example>
            </x-slot:bad>
        </x-docs.examples>

        <h3>Summary</h3>

        <p>Summarise the duties of this user, how they interact with the system, and their motivations.</p>
        
        <x-docs.examples>
            <x-slot:good>
                <x-docs.example>An anonymous visitor is one who has not authenticated with the system. They will therefore not have access to the members-only area but can still access a limited number of resources.</x-docs.example>
            </x-slot:good>
        </x-docs.examples>

        <h2 id="deleting">Deleting</h2>
        <p>Users cannot be deleted until they have been detached from all requirements.</p>
    </article>

    <x-docs.next-link href="/docs/features">Features</x-docs.next-link>
</x-layout.docs>
