{if isset($turnstile_key)}
    <script src="https://challenges.cloudflare.com/turnstile/v0/api.js" async defer></script>
    <div class="cf-turnstile-container">
        <div class="cf-turnstile" data-sitekey="{$turnstile_key}"></div>
    </div>
{/if}
