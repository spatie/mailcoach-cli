# Mailcoach CLI

A command-line tool to interact with the [Mailcoach](https://mailcoach.app) API from your terminal. Built on top of the Mailcoach OpenAPI spec, so all API endpoints are available as commands automatically.

## Installation

```bash
composer global require spatie/mailcoach-cli
```

## Getting started

### 1. Authenticate

Connect the CLI to your Mailcoach instance:

```bash
mailcoach login
```

You'll be prompted for:
- Your Mailcoach instance URL (e.g. `https://example.mailcoach.app`)
- An API token — create one at `https://example.mailcoach.app/account/api-tokens`

### 2. Run commands

Once authenticated, all API endpoints are available as commands:

```bash
mailcoach list-email-lists
mailcoach show-email-list --email-list-id=1
mailcoach list-campaigns
mailcoach show-campaign --campaign-id=1
```

Run `mailcoach` without arguments to see all available commands.

### 3. Other commands

```bash
mailcoach logout       # Remove stored credentials
mailcoach clear-cache  # Clear the cached API spec (refreshes every 24h)
```

## Credentials

Your API token and instance URL are stored in `~/.mailcoach/config.json`. Running `mailcoach logout` removes this file.

## Development

```bash
git clone git@github.com:spatie/mailcoach-cli.git
cd mailcoach-cli
composer install

php mailcoach            # Run locally
vendor/bin/pest          # Run tests
vendor/bin/phpstan analyse  # Static analysis
vendor/bin/pint          # Code style
```
