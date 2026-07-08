# WetMustardSystem Copilot Instructions

## Project Overview

- This project is a Laravel application using Vivid Architecture.
- Follow normal Laravel conventions while structuring application logic with Vivid Devices, Features, Jobs, Domains, and Operations.
- Keep code clear, testable, reusable, and separated by responsibility.

## Vivid Architecture Rules

- Keep controllers thin and focused on transport concerns only.
- A controller action should serve exactly one Feature using `$this->serve(...)`.
- A Feature represents the intent of a request.
- Features may call Jobs and Operations only.
- Features must not call other Features.
- Jobs should perform one specific task only.
- Jobs must be reusable, testable, and isolated.
- Jobs must not call other Jobs.
- Group Jobs into Domains based on the entity or business area they operate on.
- If a Job does not clearly belong to one Domain, it is probably doing too much.
- Use Operations when the same group of Jobs is commonly run together.

## Laravel and SQL Rules

- Use Eloquent or the Query Builder with bound parameters.
- Never concatenate user input into SQL.
- Avoid N+1 queries; use eager loading where appropriate.
- Use migrations for schema changes.
- Add indexes for common filters, joins, and sorts when relevant.
- Validate input with Form Requests where appropriate.
- Authorize actions with Policies or Gates.
- Keep business logic out of controllers, Blade views, and routes.

## File Placement

- Device-specific Features go in `app/Devices/<Device>/Features`.
- Global Features go in `app/Features`.
- Jobs go in `app/Domains/<Domain>/Jobs`.
- Operations go in `app/Operations`.
- Models, migrations, policies, requests, resources, and tests should follow Laravel conventions.

## When Generating Code

- State which files should be created or changed before making substantive edits.
- Prefer small, incremental changes.
- Explain which Feature, Job, Domain, or Operation each change belongs to when the structure is not obvious.
- Include migrations, model relationships, validation, authorization, and tests when relevant to the task.
- Use Vivid console commands where relevant:
  - `./vendor/bin/vivid make:feature <feature> [<device>]`
  - `./vendor/bin/vivid make:job <job> <domain>`
  - `./vendor/bin/vivid make:operation <operation>`
- If Vivid tooling is unavailable, follow the same structure manually instead of inventing a different layout.

## Avoid

- Fat controllers.
- Features calling Features.
- Jobs calling Jobs.
- Large Jobs spanning multiple Domains.
- Raw SQL unless necessary.
- Unvalidated request data.
- Business logic in Blade views.
- Hardcoded role checks spread throughout the app.
- Schema changes without migrations.

## Current Data Access Context

- The default Laravel application database is MySQL.
- Winman is a separate SQL Server connection and should stay isolated from the main app database.
- Ignore the Mint database from `MintSystemNew` when working in this project unless explicitly requested.
