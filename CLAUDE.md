# Mailcoach CLI

CLI tool for interacting with the Mailcoach API, built with Laravel Zero.

## Architecture

- **Framework**: Laravel Zero 12 (PHP 8.4+)
- **Command generation**: `spatie/laravel-openapi-cli` reads the OpenAPI spec and auto-registers commands
- **OpenAPI spec**: Fetched from `https://mailcoach.app/downloads/mailcoach-api.yaml`, cached 24h
- **Credentials**: Stored in `~/.mailcoach/config.json` (token + base URL)
- **Distribution**: PHAR binary via Box

## Key files

- `app/Services/CredentialStore.php` - Persists API token and instance URL
- `app/Services/MailcoachDescriber.php` - Custom describer with banner for list command
- `app/Providers/AppServiceProvider.php` - Registers OpenAPI CLI with Mailcoach config
- `app/Commands/LoginCommand.php` - Authentication flow (URL + token)
- `app/Commands/LogoutCommand.php` - Clear stored credentials
- `app/Commands/ClearCacheCommand.php` - Clear cached OpenAPI spec

## Commands

```bash
composer install          # Install dependencies
php mailcoach             # Show command list
php mailcoach login       # Authenticate with Mailcoach instance
php mailcoach logout      # Clear credentials
php mailcoach clear-cache # Clear cached spec
vendor/bin/pest           # Run tests
vendor/bin/phpstan analyse # Static analysis
vendor/bin/pint           # Code style
```

## Testing

Tests use Pest and are in `tests/Feature/`. Use `Http::fake()` to mock API calls in command tests. The CredentialStore tests use a temp directory to avoid touching `~/.mailcoach`.

## Important notes

- Mailcoach is self-hosted, so each user has a different base URL (unlike flare-cli)
- The CredentialStore must persist both the API token AND the instance URL
- The OpenAPI spec is fetched remotely and cached; `clear-cache` forces a re-fetch
- Auto-generated commands from the spec use `operationId`-based naming
