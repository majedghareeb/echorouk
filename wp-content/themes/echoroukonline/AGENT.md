# AGENTS.md

## Project Overview

This is a WordPress news website. The project should be developed with performance, security, SEO, editorial usability, and maintainability as top priorities.

The site may include:
- Homepage with featured stories
- Latest news sections
- Category pages
- Article pages
- Author pages
- Search
- Breaking news bar
- Newsletter integration
- Advertisement slots
- RTL first

## Development Rules

- Follow WordPress coding standards.
- Prefer native WordPress functionality before adding custom code.
- Keep the code clean, readable, and modular.
- Do not add unnecessary plugins.
- Do not modify WordPress core files.
- Do not hardcode URLs, IDs, or environment-specific values.

## Theme Guidelines

Important files may include:

- `theme.json`
- `functions.php`
- `templates/index.html`
- `templates/single.html`
- `templates/archive.html`
- `templates/search.html`
- `parts/header.html`
- `parts/footer.html`
- `patterns/`
- `assets/css/`
- `assets/js/`

## Security Requirements

Always escape output:

- `esc_html()`
- `esc_url()`
- `esc_attr()`
- `wp_kses_post()`

Always sanitize input:

- `sanitize_text_field()`
- `sanitize_email()`
- `absint()`
- `sanitize_key()`

For forms or admin actions:

- Use nonces.
- Check user capabilities.
- Validate all submitted data.

Never expose secrets, API keys, tokens, database credentials, or private configuration.

## Performance Requirements

- Avoid heavy queries.
- Use `WP_Query` carefully.
- Use pagination where needed.
- Avoid loading unnecessary scripts.
- Enqueue CSS and JavaScript properly.
- Optimize images.
- Support caching plugins.
- Avoid large homepage database queries.

## SEO Requirements

- Use semantic HTML.
- Use proper heading structure.
- Support schema where appropriate.
- Make article pages crawlable.
- Ensure clean URLs and category archives.
- Do not duplicate title or meta logic if an SEO plugin handles it.

## Accessibility Requirements

- Use accessible HTML.
- Add alt text support for images.
- Ensure buttons and links are keyboard accessible.
- Maintain sufficient color contrast.
- Do not rely on color alone to communicate meaning.

## Coding Instructions for Codex

When making changes:

1. First inspect the existing project structure.
2. Explain which files need to change.
3. Make the smallest safe change.
4. Follow WordPress standards.
5. Escape output and sanitize input.
6. Do not introduce unnecessary dependencies.
7. After changes, summarize what was changed and why.

## Testing Checklist

Before finishing a task, check:

- No PHP syntax errors.
- No obvious security issues.
- Templates render correctly.
- Mobile layout works.
- RTL layout is not broken.
- Queries are performant.
- No console errors.
- WordPress admin remains functional.

## Preferred Commands

Use these when available:

```bash
php -l path/to/file.php
