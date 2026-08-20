<x-layout :title="'Authorise '.$client->name.' - '.config('app.name', 'Spectacular')">
    <x-slot:head>
        <style>
            body {
                margin: 0;
                color: #1e2939;
                background-color: #f9fafb;
                background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='16' height='16' viewBox='0 0 16 16'%3E%3Crect x='7.5' width='0.5' height='16' fill='%23f3f4f6'/%3E%3Crect y='7.5' width='16' height='0.5' fill='%23f3f4f6'/%3E%3Crect width='0.5' height='16' fill='%23edeff2'/%3E%3Crect width='16' height='0.5' fill='%23edeff2'/%3E%3C/svg%3E");
                background-repeat: repeat;
                background-attachment: fixed;
                font-family: 'Funnel Sans', ui-sans-serif, system-ui, sans-serif;
            }

            .dark body {
                color: #9ca3af;
                background-color: #030712;
                background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='16' height='16' viewBox='0 0 16 16'%3E%3Crect x='7.5' width='0.5' height='16' fill='%23111827'/%3E%3Crect y='7.5' width='16' height='0.5' fill='%23111827'/%3E%3Crect width='0.5' height='16' fill='%231f2937'/%3E%3Crect width='16' height='0.5' fill='%231f2937'/%3E%3C/svg%3E");
            }
        </style>

        @vite(['resources/css/main.css'])
    </x-slot:head>

    <main class="flex min-h-screen items-center justify-center p-4">
        <div class="w-full max-w-lg">
            <div class="mb-6 flex justify-center">
                <a href="/" aria-label="{{ config('app.name', 'Spectacular') }}">
                    <img src="/images/logo.svg" alt="{{ config('app.name', 'Spectacular') }}" class="h-10 w-auto dark:brightness-0 dark:invert">
                </a>
            </div>

            <section class="rounded-lg bg-white shadow-lg p-4 sm:p-8 dark:bg-gray-900">
                <svg xmlns="http://www.w3.org/2000/svg" class="fill-current text-gray-600 size-16 mx-auto mb-6 dark:text-gray-300" viewBox="0 0 16 16">
                    <path d="M5.338 1.59a61 61 0 0 0-2.837.856.48.48 0 0 0-.328.39c-.554 4.157.726 7.19 2.253 9.188a10.7 10.7 0 0 0 2.287 2.233c.346.244.652.42.893.533q.18.085.293.118a1 1 0 0 0 .101.025 1 1 0 0 0 .1-.025q.114-.034.294-.118c.24-.113.547-.29.893-.533a10.7 10.7 0 0 0 2.287-2.233c1.527-1.997 2.807-5.031 2.253-9.188a.48.48 0 0 0-.328-.39c-.651-.213-1.75-.56-2.837-.855C9.552 1.29 8.531 1.067 8 1.067c-.53 0-1.552.223-2.662.524zM5.072.56C6.157.265 7.31 0 8 0s1.843.265 2.928.56c1.11.3 2.229.655 2.887.87a1.54 1.54 0 0 1 1.044 1.262c.596 4.477-.787 7.795-2.465 9.99a11.8 11.8 0 0 1-2.517 2.453 7 7 0 0 1-1.048.625c-.28.132-.581.24-.829.24s-.548-.108-.829-.24a7 7 0 0 1-1.048-.625 11.8 11.8 0 0 1-2.517-2.453C1.928 10.487.545 7.169 1.141 2.692A1.54 1.54 0 0 1 2.185 1.43 63 63 0 0 1 5.072.56"/>
                    <path d="M9.5 6.5a1.5 1.5 0 0 1-1 1.415l.385 1.99a.5.5 0 0 1-.491.595h-.788a.5.5 0 0 1-.49-.595l.384-1.99a1.5 1.5 0 1 1 2-1.415"/>
                </svg>

                <p class="mb-4">
                    <strong>{{ $client->name }}</strong> is requesting access to connect to Spectacular using your account.
                </p>

                <p class="break-words font-medium text-center mb-4">{{ $user->email }}</p>

                <div class="rounded-lg border border-gray-200 bg-gray-50 p-4 mb-4 space-y-4 dark:border-gray-700 dark:bg-gray-800">
                    <div>
                        <p class="text-sm font-medium mb-2">Permissions requested</p>

                        <ul class="space-y-2">
                            @foreach($scopes as $scope)
                                <li>
                                    <code class="text-sm font-medium">{{ $scope->id }}</code>
                                    <span class="block text-sm text-gray-600 dark:text-gray-400">{{ $scope->description }}</span>
                                </li>
                            @endforeach
                        </ul>
                    </div>

                    <div>
                        <p class="text-sm font-medium mb-1">Redirect URI</p>
                        <code class="block break-all text-sm text-gray-600 dark:text-gray-400">{{ $request->query('redirect_uri') }}</code>
                    </div>
                </div>

                <p class="mb-4">Only continue if you recognise this request. You can revoke access from your integrations later.</p>

                <div class="flex gap-4">
                    <form method="POST" action="{{ route('passport.authorizations.deny') }}" class="flex-1">
                        @csrf
                        @method('DELETE')
                        <input type="hidden" name="state" value="">
                        <input type="hidden" name="client_id" value="{{ $client->id }}">
                        <input type="hidden" name="auth_token" value="{{ $authToken }}">
                        <button type="submit" class="btn btn-primary-outline w-full">
                            Cancel
                        </button>
                    </form>

                    <form method="POST" action="{{ route('passport.authorizations.approve') }}" class="flex-1" id="authorizeForm">
                        @csrf
                        <input type="hidden" name="state" value="">
                        <input type="hidden" name="client_id" value="{{ $client->id }}">
                        <input type="hidden" name="auth_token" value="{{ $authToken }}">
                        <button type="submit" class="btn btn-primary w-full" id="authorizeButton">
                            <svg id="loadingSpinner" class="hidden size-4 animate-spin text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" aria-hidden="true">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            <span id="authorizeText">Authorise</span>
                        </button>
                    </form>
                </div>
            </section>
        </div>
    </main>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const form = document.getElementById('authorizeForm');
        const button = document.getElementById('authorizeButton');
        const authorizeText = document.getElementById('authorizeText');
        const loadingSpinner = document.getElementById('loadingSpinner');

        form.addEventListener('submit', function(e) {
            // Show loading state...
            button.disabled = true;
            authorizeText.textContent = 'Authorising...';
            loadingSpinner.classList.remove('hidden');

            // After form submission, watch for redirect and close window...
            setTimeout(function() {
                const checkRedirect = setInterval(function() {
                    // If URL changed or we have OAuth params, redirect happened...
                    if (!window.location.href.includes('/oauth/authorize') ||
                        window.location.search.includes('code=') ||
                        window.location.search.includes('error=')) {
                        clearInterval(checkRedirect);
                        window.close();
                    }
                }, 100);

                // Fallback: Close after five seconds...
                setTimeout(function() {
                    clearInterval(checkRedirect);
                    window.close();
                }, 5000);
            }, 200);
        });

        // Handle cancel button...
        const cancelForm = document.querySelector('form[method="POST"]:has(input[name="_method"][value="DELETE"])');
        if (cancelForm) {
            cancelForm.addEventListener('submit', function(e) {
                setTimeout(function() {
                    window.close();
                }, 200);
            });
        }
    });
</script>
</x-layout>
