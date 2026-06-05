<x-layout.docs title="AI-assisted Development">
    <article class="prose">
        <h1>AI-assisted Development</h1>

        <p>While Spectacular puts humans first, it's very good at steering AI coding agents. The specification is not just a planning document or project record anymore; it is structured context that agents can query while they work.</p>

        <h2 id="workflow">Recommended workflow</h2>

        <p>A specification is a far better interface for AI-assisted development than the traditional toilet-roll chat window. Here's how to do it:</p>

        <ol>
            <li>Create a new Spectacular specification as you would for a human. Identify the users, features and requirements so the agent has a context to work within.</li>
            <li>Where necessary, provide subtasks to help steer the implementation.</li>
            <li>Connect your coding agent to Spectacular through the MCP interface.</li>
            <li>Ask the agent to inspect the relevant specification. This is a good time to use the planning mode.</li>
            <li>Set it to work and then inspect the output.</li>
            <li>Update the specification where it has failed due to ambiguity and run the build again until satisfied.</li>
        </ol>

        <p>The agent should be smart enough to only request changes during the iterative process, saving on tokens.</p>

        <h2 id="prototyping">Rapid Prototying</h2>

        <p>Even if you intend to implement the specification with human developers, the workflow above is an excellent way to discover holes in your specification. By mocking up the applpication in this way, you often find requirements you missed that may be costly to add later.</p>

        <h2 id="connect-agents">Connect agents with MCP</h2>

        <p>The Spectacular MCP server lets AI coding agents read your specifications directly. While Spectacular can just export the whole specification as Markdown, the MCP server makes the process transparent as the agent is able to read it directly. The agent is able to query for your most recent changes to the specification, maintaining the context while saving tokens.</p>

        <h2 id="server-url">Connecting</h2>

        <p>Anyone with a Spectacular account can use the MCP server to interogate their specifications using the following URL.</p>

        <pre><code>https://spec.tacul.ar/mcp/specifications</code></pre>

        <p>We have agent-specific instructions for <a href="/docs/mcp-setup/codex">Codex</a>, <a href="/docs/mcp-setup/cursor">Cursor</a> and <a href="/docs/mcp-setup/claude">Claude</a>.</p>

        <h2 id="authentication">Authentication</h2>

        <p>Most agents will attempt authentication using a standard OAuth flow like those of social logins. Simply follow the browser sign-in flow when your client asks you to authenticate.</p>

        <p>If your client does not support OAuth for remote MCP servers, simpole bearer tokens are available in your account's <a href="/app/account/integrations">integrations</a> section. Pass the token as a bearer token in the HTTP authorization header:</p>

        <pre><code>Authorization: Bearer YOUR_SPECTACULAR_TOKEN</code></pre>

        <h2 id="available-tools">Available tools</h2>

        <p>The Spectacular MCP server currently exposes read-only tools. Agents can inspect specifications, but they cannot edit projects through MCP yet.</p>

        <table>
            <thead>
                <tr>
                    <th>Tool</th>
                    <th>What it does</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td><code>ListProjectsTool</code></td>
                    <td>Lists the specifications available to the authenticated account.</td>
                </tr>
                <tr>
                    <td><code>GetProjectTool</code></td>
                    <td>Returns a full specification, including users, features, requirements, tasks, blockers, and unknowns.</td>
                </tr>
                <tr>
                    <td><code>GetItemTool</code></td>
                    <td>Returns one project, user, feature, requirement, task, assignment, or unknown. It can include history since a timestamp.</td>
                </tr>
                <tr>
                    <td><code>GetChangesTool</code></td>
                    <td>Returns project entities that changed since an ISO 8601 timestamp.</td>
                </tr>
            </tbody>
        </table>

        <h2 id="test-prompts">Prompts</h2>

        <p>After setup, ask your agent to prove it can reach Spectacular:</p>

        <pre><code>Can you list my Spectacular specifications?</code></pre>

        <pre><code>Use Spectacular to read the specification for this codebase before you propose a plan.</code></pre>

        <pre><code>Check Spectacular for unknowns or blockers that affect this change.</code></pre>
    </article>

    <x-docs.next-link href="/docs/mcp-setup/codex">Codex Setup</x-docs.next-link>
</x-layout.docs>
