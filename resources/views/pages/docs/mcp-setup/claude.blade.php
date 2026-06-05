<x-layout.docs title="Claude MCP Setup">
    <article class="prose">
        <h1>Setup for Claude</h1>

        <p>Connect the Spectacular MCP server to Claude Code using remote HTTP transport.</p>

        <h2 id="prerequisites">Prerequisites</h2>

        <ul>
            <li>Claude Code installed on your computer.</li>
            <li>A Spectacular account with a verified email address.</li>
            <li>Access to the Spectacular project you want Claude to inspect.</li>
        </ul>

        <h2 id="oauth">Step 1: Add the server with OAuth</h2>

        <p>Run this command in your project directory:</p>

        <pre><code>claude mcp add --transport http spectacular https://spec.tacul.ar/mcp/specifications</code></pre>

        <p>Then start Claude Code, run <code>/mcp</code>, and complete the browser authentication flow.</p>

        <h2 id="user-scope">Optional: Add globally</h2>

        <p>To make Spectacular available across Claude Code projects on the same machine, add <code>--scope user</code>:</p>

        <pre><code>claude mcp add --scope user --transport http spectacular https://spec.tacul.ar/mcp/specifications</code></pre>

        <h2 id="bearer-token">Alternative: Bearer-token authentication</h2>

        <p>If OAuth is not available, create a token in <a href="/app/account/integrations">Account integrations</a> and pass it as an authorization header:</p>

        <pre><code>claude mcp add --transport http spectacular https://spec.tacul.ar/mcp/specifications \
  --header "Authorization: Bearer YOUR_SPECTACULAR_TOKEN"</code></pre>

        <h2 id="verify">Step 2: Verify the connection</h2>

        <ol>
            <li>Run <code>claude mcp list</code> and confirm <code>spectacular</code> appears.</li>
            <li>Run <code>/mcp</code> inside Claude Code and confirm Spectacular is connected.</li>
            <li>Ask Claude: <code>Can you list my Spectacular specifications?</code></li>
        </ol>

        <h2 id="claude-desktop">Claude web and desktop</h2>

        <p>Claude web and desktop can connect to remote MCP servers through custom connectors where available. Add the Spectacular MCP URL in Claude's connector settings and complete authentication.</p>

        <p>If you are self-hosting Spectacular on a private or local URL, use Claude Code or expose the server through a secure HTTPS endpoint that Claude can reach.</p>
    </article>

    <x-docs.next-link href="/docs/projects">Projects</x-docs.next-link>
</x-layout.docs>
