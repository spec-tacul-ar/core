<x-layout.docs title="Get started">
    <article class="prose max-w-none">
        <header class="not-prose mb-10 border-b border-gray-100 pb-8">
            <h1 class="font-display mb-4 text-5xl font-semibold leading-tight text-gray-950">Get started</h1>
            <p class="max-w-2xl text-lg leading-8 text-gray-500">Choose Spectacular Cloud for a managed service, or self-host the Laravel application with PHP or Docker.</p>
        </header>

        <h2 id="cloud">Cloud</h2>
        <p>Spectacular Cloud is the simplest way to start. Hosting, updates, backups, and operational maintenance are handled for you, so the team can focus on writing and reviewing specifications.</p>
        <p>Use Cloud when you want shared access without running infrastructure, or when stakeholders need a stable place to review projects immediately.</p>

        <h2 id="self-hosting">Self-hosting</h2>
        <p>Self-hosting gives you control over deployment, data storage, and configuration. It is best for teams already comfortable maintaining Laravel applications or internal Docker services.</p>

        <h3 id="php">PHP</h3>
        <p>The PHP installation runs Spectacular directly as a Laravel application.</p>
        <ul>
            <li>PHP 8.2 or newer.</li>
            <li>Composer 2.</li>
            <li>Node.js and npm.</li>
            <li>PHP extensions: BCMath, PDO, SQLite/PDO SQLite, and Zip.</li>
            <li>Git and unzip.</li>
        </ul>

        <pre><code>git clone https://github.com/syntheticminds/spectacular.git
cd spectacular
composer setup
php artisan serve</code></pre>

        <h3 id="docker">Docker</h3>
        <p>Docker is the quickest self-hosted path when you do not want to install PHP, Composer, and Node directly. The supplied Compose file builds the app and serves it on port 8000.</p>

        <pre><code>docker compose up --build</code></pre>
    </article>
</x-layout.docs>
