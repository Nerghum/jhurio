<laravel-boost-guidelines>
=== foundation rules ===

# Laravel Boost Guidelines

## Foundational Context

This application is a Laravel application running on PHP 8.5. Always use the APIs that match the installed major version of each package — do not assume a version.

Before relying on a package's API, confirm its installed version:
- PHP packages: run `composer show --direct` to list direct dependencies with versions, or `composer show <vendor/package>` for a single package.
- JS packages: check `package.json` for the installed versions.

## Conventions

- You must follow all existing code conventions used in this application. When creating or editing a file, check sibling files for the correct structure, approach, and naming.
- Use descriptive names for variables and methods. For example, `isRegisteredForDiscounts`, not `discount()`.
- Check for existing components to reuse before writing a new one.

## Application Structure & Architecture

- Stick to existing directory structure; don't create new base folders without approval.
- Do not change the application's dependencies without approval.

## Frontend Bundling

- If a frontend change doesn't show in the UI or you get a "Unable to locate file in Vite manifest" error, run `npm run build` or ask the user to run `npm run dev` or `composer run dev`.

=== php rules ===

# PHP

- Always use curly braces for control structures, even for single-line bodies.
- Use PHP 8 constructor property promotion: `public function __construct(public GitHub $github) { }`. Do not leave empty zero-parameter `__construct()` methods unless the constructor is private.
- Use explicit return type declarations and type hints for all method parameters: `function isAccessible(User $user, ?string $path = null): bool`
- Use TitleCase for Enum keys: `FavoritePerson`, `BestLake`, `Monthly`.
- Prefer PHPDoc blocks over inline comments. Only add inline comments for exceptionally complex logic.
- Use array shape type definitions in PHPDoc blocks.

=== laravel/core rules ===

# Do Things the Laravel Way

- Use `php artisan make:` commands to create new files (i.e. migrations, controllers, models, etc.). You can list available Artisan commands using `php artisan list` and check their parameters with `php artisan [command] --help`.
- If you're creating a generic PHP class, use `php artisan make:class`.
- Pass `--no-interaction` to all Artisan commands to ensure they work without user input. You should also pass the correct `--options` to ensure correct behavior.

## URL Generation

- When generating links to other pages, prefer named routes and the `route()` function.

=== pint/core rules ===

# Laravel Pint Code Formatter

- If you have modified any PHP files, you must run `vendor/bin/pint --dirty --format agent` before finalizing changes to ensure your code matches the project's expected style.

=== phpunit/core rules ===

# PHPUnit

=== product upload rules ===

# Product Upload & Store Management via CLI / API (Jhurio Live Site)

- Target Site: https://jhurio.com
- API Endpoint: https://jhurio.com/api
- Authorization: Bearer prozRcAArjlDDhV8ZRI3qeVFKRYsIy8c
- Dedicated CLI Tool: `./jhurio-cli` or `php artisan jhurio:cli`
- Full API & CLI Guide: See [API_DOCUMENTATION.md](file:///Users/ashik/Desktop/personal%20work/jhurio/API_DOCUMENTATION.md)

CLI Quick Commands:
- `./jhurio-cli ping` (health check)
- `./jhurio-cli stats` (live store metrics)
- `./jhurio-cli products list` (view live products table)
- `./jhurio-cli products update <id> --price <val> --stock <val>`
- `./jhurio-cli orders list --status pending`
- `./jhurio-cli orders set-status <id> shipped`
- `./jhurio-cli homepage set-hero --slider-image "<url>" --slider-link "/shop"`
- `./jhurio-cli banners list` / `./jhurio-cli banners create --type slider --image-url "<url>"`
- `./jhurio-cli settings set <key> <val>`

When the user provides a product URL to upload/import:
1. Scrape product data and images.
2. Remove image background with Python rembg, crop and center cleanly.
3. Save images to public/assets/images/products/<slug>/ and git push to main.
4. Rewrite title, short description, and full content in engaging Bangla (do not directly copy).
5. Include 2 Bangla image_text_sections with images and CTA buttons.
6. Post product via CLI `./jhurio-cli products create --data '<json>'` or POST to https://jhurio.com/api/products and verify the live product URL.

</laravel-boost-guidelines>
