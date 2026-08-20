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

        <h2 id="prototyping">Rapid Prototyping</h2>

        <p>Even if you intend to implement the specification with human developers, the workflow above is an excellent way to discover holes in your specification. By mocking up the application in this way, you often find requirements you missed that may be costly to add later.</p>

        <h2 id="connect-agents">Connect agents with MCP</h2>

        <p>The Spectacular MCP server lets AI coding agents read your specifications directly. While Spectacular can just export the whole specification as Markdown, the MCP server makes the process transparent as the agent is able to read it directly. The agent is able to query for your most recent changes to the specification, maintaining the context while saving tokens.</p>

        <h2 id="server-url">Connecting</h2>

        <p>Anyone with a Spectacular account can use the MCP server to interrogate their specifications using the following URL.</p>

        <pre><code>https://spec.tacul.ar/mcp/specifications</code></pre>

        <p>We have agent-specific instructions for <a href="/docs/mcp/codex">Codex</a>, <a href="/docs/mcp/cursor">Cursor</a> and <a href="/docs/mcp/claude">Claude</a>.</p>

        <h2 id="authentication">Authentication</h2>

        <p>Most agents will attempt authentication using a standard OAuth flow like those of social logins. Simply follow the browser sign-in flow when your client asks you to authenticate.</p>

        <p>If your client does not support OAuth for remote MCP servers, simple bearer tokens are available in your account's <a href="/app/account/integrations">integrations</a> section. Pass the token as a bearer token in the HTTP authorization header:</p>

        <pre><code>Authorization: Bearer YOUR_SPECTACULAR_TOKEN</code></pre>

        <h2 id="available-tools">Available tools</h2>

        <p>The Spectacular MCP server lets agents inspect specifications and update requirement completion.</p>

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
                <tr>
                    <td><code>SetRequirementCompletionTool</code></td>
                    <td>Marks an unblocked requirement as complete or reopens it.</td>
                </tr>
            </tbody>
        </table>

        <h2 id="example-prompts">Example Prompts</h2>

        <p>The real advantage is not just giving an agent a one-off brief. Spectacular gives it the full specification: users, features, requirements, tasks, unresolved questions, blockers, assignments, comments, and recent changes. That lets the agent keep checking its work against the product you actually meant to build, especially later in development when the codebase and the plan have both started to move.</p>

        <pre><code>Can you list my Spectacular specifications?</code></pre>

        <pre><code>Read the Spectacular specification for this project before you propose an implementation plan. Identify the users, the relevant feature, and any requirements or unknowns that should shape the work.</code></pre>

        <pre><code>Before changing code, check Spectacular for blockers, unknowns, or recent specification changes that affect this feature. If anything is ambiguous, tell me what needs clarifying before you implement.</code></pre>

        <pre><code>Compare the current implementation with the Spectacular requirements for this feature. Point out any missing behaviours, edge cases, or assumptions that do not match the specification.</code></pre>

        <pre><code>Use Spectacular to find the requirements related to this bug. Explain which requirement is being violated, then make the smallest code change that brings the implementation back into line.</code></pre>

        <pre><code>Check what has changed in Spectacular since yesterday and tell me whether this branch needs to be updated before we continue.</code></pre>

        <pre><code>After implementing this feature, review the Spectacular tasks and requirements again. Confirm what is complete, what still needs testing, and whether any unknowns should be added back to the specification.</code></pre>
    </article>

    <x-docs.next-link href="/docs/projects">Projects</x-docs.next-link>
</x-layout.docs>
