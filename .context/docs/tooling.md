# Tooling & Productivity Guide

## Tooling & Productivity Guide
This guide outlines the tools and scripts that streamline development for Fiscaut v4.1.

## Contexto do Projeto
- **Produto**: aplicação comercial proprietária.
- **Stack**: Laravel v12, FilamentPHP v5 e Livewire v4.

## Required Tooling
- **Laravel Sail**: A light-weight command-line interface for interacting with Laravel's default Docker development environment.
- **Composer**: Dependency Manager for PHP.
- **Node.js & NPM**: For building frontend assets.
- **Git**: Version control.

## Recommended Automation
- **Laravel Pint**: An opinionated PHP code style fixer.
    - Run: `./vendor/bin/sail bin pint`
- **Larastan**: PHPStan wrapper for Laravel.
    - Run: `./vendor/bin/sail bin phpstan analyse`
- **Ide Helper**: Generates helper files for IDE autocompletion (if installed).
    - Run: `./vendor/bin/sail artisan ide-helper:generate`

## IDE / Editor Setup
**VS Code Recommended Extensions**:
- **PHP Intelephense**: Essential PHP support.
- **Laravel Blade Snippets**: Syntax highlighting for Blade.
- **Laravel Artisan**: Run Artisan commands from the command palette.
- **Tailwind CSS IntelliSense**: Autocomplete for utility classes.
- **EditorConfig**: Ensures consistent coding styles across editors.

## Productivity Tips
- **Sail Alias**: Add `alias sail='[ -f sail ] && bash sail || bash vendor/bin/sail'` to your shell profile to run `sail` directly.
- **Tinker**: Use `./vendor/bin/sail artisan tinker` to interact with your application's objects in a REPL environment.

## Cross-References
- [development-workflow.md](./development-workflow.md)
