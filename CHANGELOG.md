# Changelog

## 1.0.2 - 2026-11-06

### What's Changed

- Fix CLI startup on PHP 8.6 by upgrading the bundled Laravel Zero runtime and requiring the Illuminate Container bootstrap fix.
- Rebuild the distributed PHAR while preserving PHP 8.4 support, including PHP 8.4.0.
- Align the source CLI and packaged binary version with 1.0.2.
- Verify source tests, packaged CLI smoke checks, and PHAR builds on PHP 8.4, 8.5, and the current PHP 8.6 development runtime, with Ubuntu and Windows coverage.

**Full Changelog**: https://github.com/spatie/mailcoach-cli/compare/1.0.1...1.0.2
