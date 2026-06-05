<x-layout.docs title="Codex MCP Setup">
    <article class="prose">
        <h1>Setup for Codex</h1>

        <p>Connect the Spectacular MCP server to Codex using remote HTTP transport.</p>

        <h2 id="prerequisites">Prerequisites</h2>

        <ul>
            <li>Codex CLI or Codex IDE extension installed.</li>
            <li>A Spectacular account with a verified email address.</li>
            <li>Access to the Spectacular project you want Codex to inspect.</li>
        </ul>

        <h2 id="oauth">Step 1: Add the server with OAuth</h2>

        <p>Run these commands in your terminal:</p>

        <pre><code>codex mcp add spectacular --url https://spec.tacul.ar/mcp/specifications
codex mcp login spectacular</code></pre>

        <p>Codex will open a browser so you can authorize access to Spectacular.</p>

        <h2 id="manual-config">Alternative: Manual configuration</h2>

        <p>Codex stores MCP configuration in <code>~/.codex/config.toml</code>. Add the Spectacular server:</p>

        <pre><code>[mcp_servers.spectacular]
url = "https://spec.tacul.ar/mcp/specifications"</code></pre>

        <p>For bearer-token authentication, create a token in <a href="/app/account/integrations">Account integrations</a>, store it in an environment variable, and reference it from the same file:</p>

        <pre><code>[mcp_servers.spectacular]
url = "https://spec.tacul.ar/mcp/specifications"
bearer_token_env_var = "SPECTACULAR_TOKEN"</code></pre>

        <h2 id="verify">Step 2: Verify the connection</h2>

        <ol>
            <li>Run <code>codex mcp list</code> and confirm <code>spectacular</code> appears.</li>
            <li>Open a Codex session and run <code>/mcp</code>.</li>
            <li>Ask Codex: <code>Can you list my Spectacular specifications?</code></li>
        </ol>

        <h2 id="usage">Step 3: Use Spectacular while coding</h2>

        <p>Before asking Codex to edit code, ask it to inspect the specification:</p>

        <pre><code>Use Spectacular to read the relevant specification, then summarize the requirements and blockers before you plan code changes.</code></pre>
    </article>

    <x-docs.next-link href="/docs/mcp/cursor">Cursor Setup</x-docs.next-link>
</x-layout.docs>
