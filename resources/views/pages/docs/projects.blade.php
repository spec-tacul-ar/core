<x-layout.docs title="Projects">
    <article class="prose">
        <h1>Projects</h1>

        <p>Create a project for anything that needs a coherent software specification. This could be a SaaS product, mobile app, internal tool, public API or an upgrade to existing software.</p>

        <p>The specification you write should answer the following questions:</p>

        <ul>
            <li>What are we building?</li>
            <li>Who is it for?</li>
            <li>What features are in scope?</li>
            <li>What requirements define those features?</li>
            <li>What is still unknown?</li>
            <li>Who is contributing to the specification?</li>
        </ul>

        <p>Remember that Spectacular project is not a filing cabinet: it is a discovery tool. Use writing the specification as an opportunity to find gaps in knowledge. Keep these thoughts in mind:</p>

        <ul>
            <li>Which users haven't we thought of?</li>
            <li>Which features have been implied but not written down?</li>
            <li>Which requirements depend on unmade decisions?</li>
            <li>Which edge cases have no agreed behaviour?</li>
            <li>Which tasks have being planned before the requirement is clear?</li>
            <li>Which assumptions would be most expensive if wrong?</li>
        </ul>

        <p>The earlier these questions are captured, the cheaper they are to resolve.</p>

        <x-docs.tip>
            While it's tempting to thrash-out a specification in no time using AI, you are outsourcing your thinking and may miss critical requirements. The AI doesn't <em>understand</em> motivations like humans do and will make odd judgements.
        </x-docs.tip>

        <h2 id="fields">Fields</h2>

        <h3>Name</h3>

        <p>Use a name that will still make sense to you and your collaborators both now and in the future.</p>

        <x-docs.examples>
            <x-slot:good>
                <x-docs.example>Acme Customer Portal</x-docs.example>
                <x-docs.example>Advent's Onboarding System</x-docs.example>
                <x-docs.example>Internal Support Dashboard</x-docs.example>
                <x-docs.example>Mobile App v2</x-docs.example>
            </x-slot:good>
            
            <x-slot:bad>
                <x-docs.example>New app</x-docs.example>
                <x-docs.example>Stuff to build</x-docs.example>
                <x-docs.example>MVP</x-docs.example>
                <x-docs.example>Client project</x-docs.example>
            </x-slot:bad>
        </x-docs.examples>

        <h3>Summary</h3>

        <p>The project summary gives you a space to answer some of the questions above: motivation, general scope and expected outcome.</p>

        <x-docs.examples>
            <x-slot:good>
                <x-docs.example>This project defines the customer dashboard for self-service account management. It should reduce support requests by allowing account owners to manage billing, invoices, team access, and subscription changes without contacting support.</x-docs.example>
            </x-slot:good>
            
            <x-slot:bad>
                <x-docs.example>This project is for the dashboard.</x-docs.example>
            </x-slot:bad>
        </x-docs.examples>

        <p>This is a rich text field so feel free to add as much details as you think might be needed. It's a good place to mention things that are not within scope.</p>

        <h2 id="organising">Organising</h2>

        <p>As your specification grows, you'll find better ways to arrange it. Clicking the <em>Organise</em> button in the navbar to reveal a sidebar that lists the project's users and features. Features can be further expanded to reveal their requirements. Requirements can also be reorganised and even dragged to other features.</p>

        <h2 id="exporting">Exporting</h2>

        <p>The <em>Export</em> button in the navbar reveals the options to download your project as HTML, Markdown and JSON. These links are only available to collaborators.</p>

        <p>If you're running Spectacular locally, you can copy the link to the Markdown export and provide it directly to your AI coding agent.</p>

        <h2 id="filtering">Filtering</h2>

        <p>When specifications get large, it's helpful to be able to drill down to the requirements you need for the day. Clicking the filter button in the navbar will reveal a sidebar with a number of filter options.</p>

        <p>In addition to hiding certain features, you can create combinations of requirement status and user fields. Each section can be reset with a button press.</p>

        <x-docs.tip>If you find you're suddenly missing a lot of requirements, you've probably left a filter on.</x-docs.tip>

        <h2 id="deleting">Deleting</h2>

        <p>A project can be deleted from the edit sidepanel. Beware that this is an irreversable action - Spectacular does not keep copies of your projects once deleted. Be sure to export your data if there is a possibility that you might want to recover it.</p>
    </article>

    <x-docs.next-link href="/docs/users-and-features">Users &amp; features</x-docs.next-link>
</x-layout.docs>
