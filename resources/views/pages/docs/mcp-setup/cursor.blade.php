<x-layout.docs title="Cursor MCP Setup">
    <article class="prose">
        <h1>Setup for Cursor</h1>

        <p>Connect the Spectacular MCP server to Cursor using remote HTTP transport.</p>

        <h2 id="prerequisites">Prerequisites</h2>

        <ul>
            <li>Cursor installed on your computer.</li>
            <li>A Spectacular account with a verified email address.</li>
            <li>Access to the Spectacular project you want Cursor to inspect.</li>
        </ul>

        <h2 id="manual-config">Step 1: Add the MCP server</h2>

        <p>Add the following configuration to Cursor:</p>

        <ul>
            <li>Project-specific: <code>.cursor/mcp.json</code> in your project root.</li>
            <li>Global: <code>~/.cursor/mcp.json</code>.</li>
        </ul>

        <pre><code>{
  "mcpServers": {
    "spectacular": {
      "url": "https://spec.tacul.ar/mcp/specifications"
    }
  }
}</code></pre>

        <p>You can also add the server through Cursor settings by creating a new MCP server and using the Spectacular URL.</p>

        <h2 id="bearer-token">Alternative: Bearer-token authentication</h2>

        <p>If OAuth is not available in your Cursor setup, create a token in <a href="/app/account/integrations">Account integrations</a>, store it in <code>SPECTACULAR_TOKEN</code>, and add the authorization header:</p>

        <pre><code>{
  "mcpServers": {
    "spectacular": {
      "url": "https://spec.tacul.ar/mcp/specifications",
      "headers": {
        "Authorization": "Bearer ${env:SPECTACULAR_TOKEN}"
      }
    }
  }
}</code></pre>

        <h2 id="verify">Step 2: Verify the connection</h2>

        <ol>
            <li>Restart Cursor or reload the window.</li>
            <li>Open Cursor settings and confirm the Spectacular MCP server is enabled.</li>
            <li>Check MCP logs if the server does not connect.</li>
            <li>Ask Cursor: <code>Can you list my Spectacular specifications?</code></li>
        </ol>

        <h2 id="usage">Step 3: Use Spectacular in chat</h2>

        <p>Ask Cursor to read the specification before planning or editing:</p>

        <pre><code>Use the Spectacular MCP server to inspect this project's requirements before changing code.</code></pre>
    </article>

    <x-docs.next-link href="/docs/mcp-setup/claude">Claude Setup</x-docs.next-link>
</x-layout.docs>
