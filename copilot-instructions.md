
# WetMustardSystem Copilot Instructions

## Purpose

These instructions align WetMustardSystem with the same operational design pattern used across Mint-style systems: strict admin controls, configuration-driven email reporting, and controlled paperwork metadata.

Build for production safety, operational traceability, and maintainability.

## Core Behaviour

GitHub Copilot should:

- Generate production-ready code.
- Keep changes small and scoped to the request.
- Reuse existing architecture and naming patterns.
- Prioritise readability, testability, and security.
- Include meaningful validation, error handling, and logging.

GitHub Copilot should not:

- Invent business rules, statuses, workflows, recipients, or approvals.
- Hardcode admin users, email addresses, or document metadata.
- Mix unrelated concerns in one class or one action.

When requirements are unclear, ask for clarification before implementing.

## Architecture Pattern (Laravel + Vivid)

- Controllers must stay thin and call one Feature via $this->serve(...).
- A Feature represents one request intent.
- Features may call Jobs and Operations only.
- Features must not call other Features.
- Jobs must do one task, remain reusable, and must not call other Jobs.
- Group Jobs by Domain in app/Domains/<Domain>/Jobs.
- Use Operations in app/Operations for reusable multi-step orchestration.

## Admin Access Pattern

- Use Policies/Gates/Form Requests for authorisation and validation.
- Keep admin checks centralised; do not spread role/email checks across views or controllers.
- Treat the following as admin-managed configuration areas:
  - Operator visibility and enable/disable flags.
  - Email report catalogue, schedule metadata, and recipient lists.
  - Paperwork/document control metadata (issue/version history).
- Log all admin configuration changes with actor, timestamp, action, and before/after values where practical.

## Emailing System Pattern

- Follow a configuration-driven model for scheduled reporting.
- Keep report definitions and recipients in database configuration, not in hardcoded code paths.
- Store OAuth and email transport settings in secure configuration (environment variables and/or encrypted secrets), not in source code.
- Support:
  - Per-report enable/disable.
  - Global/default settings.
  - To/CC recipient types.
  - Per-recipient enable/disable.
- Separate report generation from email dispatch.
- Prefer queueable jobs for dispatch; keep transport concerns inside mail operations (for example Office 365 operation classes).
- Scheduled execution should be compatible with server-side schedulers (for example Task Scheduler/cron) instead of requiring manual runs.

## Paperwork and QA Document Pattern

- Treat paperwork metadata as controlled data, not hardcoded UI text.
- Keep a document-control source of truth keyed by report type and relevant context (such as customer/container variants).
- Each controlled document/report should support:
  - Title.
  - Issue/version.
  - Date issued.
  - Issued by.
  - Reason for change.
  - Active flag.
- Provide default metadata rows plus override rows for specific customer/container contexts.
- Enforce uniqueness constraints for context keys and preserve change history/auditability.

## Data and SQL Rules

- Default application database is MySQL.
- Winman SQL Server must remain isolated as a separate connection.
- Do not mix cross-database side effects in one flow unless explicitly required and safely designed.
- Use Eloquent or Query Builder with bound parameters.
- Never concatenate user input into SQL.
- Avoid N+1 queries through eager loading where appropriate.
- Use migrations for schema changes and add indexes for common filters/joins/sorts.

## File Placement

- Device Features: app/Devices/<Device>/Features.
- Global Features: app/Features.
- Domain Jobs: app/Domains/<Domain>/Jobs.
- Operations: app/Operations.
- Models, requests, policies, resources, migrations, and tests follow Laravel conventions.

## Delivery Expectations

- State which files will change before substantive edits.
- Prefer incremental changes over large rewrites.
- Explain which Feature/Job/Operation each change belongs to when not obvious.
- Include migrations, validation, authorisation, and tests when relevant.
- Use Vivid generators when available:
  - ./vendor/bin/vivid make:feature <feature> [<device>]
  - ./vendor/bin/vivid make:job <job> <domain>
  - ./vendor/bin/vivid make:operation <operation>

## Avoid

- Fat controllers.
- Feature-to-Feature calls.
- Job-to-Job calls.
- Hardcoded admin/recipient values in source code.
- Business rules inside Blade templates.
- Unvalidated input and unauthorised writes.
- Schema changes without migrations.

## Current Data Access Context

- WetMustard app data uses MySQL by default.
- Winman remains a separate SQL Server integration.
- Do not pull from MintSystemNew databases unless explicitly requested.
- Use a shared `style.css` per project that defines `header`, `.header-container`, `.logo`, and `nav ul li a` styles consistent with the TraceNewGen design.

### Reference Implementation

See `main.php` and `style.css` in the TraceNewGen project (`c:\xampp\htdocs\TraceNewGen`) for the canonical header/nav implementation to replicate in new projects.

---

# DataTables Standard

When displaying tabular data:

Enable:

- Searching
- Sorting
- Pagination
- Export to Excel
- Export to CSV
- Column Visibility

Default page size:

```javascript
25
```

---

# AJAX Standards

Prefer AJAX over full page reloads.

All AJAX responses should use a consistent format.

Success:

```json
{
  "success": true,
  "message": "Operation completed successfully",
  "data": {}
}
```

Failure:

```json
{
  "success": false,
  "message": "Validation failed"
}
```

---

# Authentication Standards

If authentication is required, prefer:

1. Microsoft Entra ID
2. Microsoft Graph
3. Internal user tables (fallback)

Suggested roles:

```text
Administrator
Manager
User
Read Only
```

---

# AutoMail Standards (Optional)

If automated email functionality is required, use Microsoft Graph API with app-only authentication.

Prefer:

- Microsoft Graph API
- OAuth2 Client Credentials Flow
- HTML Email Templates

Avoid SMTP unless explicitly requested.

---

## Graph Configuration Structure

Store configuration separately from business logic.

Example:

```php
define('GRAPH_TENANT_ID', '');
define('GRAPH_CLIENT_ID', '');
define('GRAPH_CLIENT_SECRET', '');

define('MAIL_FROM', '');
define('MAIL_FROM_NAME', '');

define('MAIL_TO', '');
```

Required storage rules:

- OAuth tenant/client identifiers may be stored in config tables when needed for admin visibility, but secrets/tokens must be stored as encrypted secrets.
- Client secrets, refresh tokens, and private keys must never be hardcoded in PHP files, JavaScript files, or Blade templates.
- Email sender defaults, report recipient lists, and schedule metadata must be database-driven and admin-managed.
- Use environment variables for secret references and rotate credentials without code changes.

---

## Email Functions

Create reusable helper functions.

Preferred design:

```php
sendEmail(
    $to,
    $subject,
    $htmlBody,
    $cc = [],
    $bcc = [],
    $attachments = []
);
```

Do not duplicate Graph authentication code throughout the project.

---

## Standard Email Types

Support generation of:

- Error Notifications
- Workflow Notifications
- Approval Requests
- Audit Alerts
- Scheduled Reports
- Status Updates

---

# Approval Workflow Standards

When approvals are required, default statuses should be:

```text
Draft
Submitted
In Review
Approved
Rejected
Archived
```

Track:

- Approver
- Approval Date
- Comments

---

# Reporting Standards

Support:

- Excel
- CSV

Optional:

- PDF

Filename format:

```text
ReportName_YYYYMMDD_HHMMSS
```

---

# Dashboard Standards

Dashboard pages should include:

- KPI Cards
- Recent Activity
- Outstanding Actions
- Quick Filters
- Key Navigation Links

---

# Performance Standards

Prefer:

- Indexed search columns
- Explicit column selection
- Efficient joins
- Parameterised queries

Avoid:

- SELECT *
- Unnecessary database calls
- Duplicate data retrieval

Only retrieve required data.

---

# Documentation Standards

Major features should include documentation covering:

- Purpose
- Business Logic
- Database Changes
- Dependencies
- Configuration Requirements

README files should be updated when significant functionality is introduced.

---

# Environment Standards

Applications should support:

```text
Development
Test
Production
```

Configuration should be environment-specific where practical.

---

# Git and Source Control Standards

## General Rule

GitHub Copilot is responsible for generating and modifying code only.

Source control decisions remain the responsibility of the developer.

---

## Never Automatically

GitHub Copilot should not automatically:

- Commit changes
- Push changes
- Create branches
- Create pull requests
- Modify GitHub Actions
- Modify CI/CD pipelines
- Merge branches

Unless explicitly instructed.

---

## Major Refactoring or Project Overhauls

For significant changes including:

- Large-scale refactoring
- Database redesign
- Architecture changes
- Framework migrations
- Folder reorganisation
- Major UI redesigns

GitHub Copilot should:

1. Explain the proposed change.
2. List impacted files.
3. Identify risks.
4. Recommend an implementation approach.
5. Obtain approval before proceeding.

---

## Change Scope Control

Modify only files directly related to the requested task.

Do not:

- Reformat unrelated files.
- Rename files unnecessarily.
- Rewrite stable functionality without reason.
- Remove legacy functionality unless explicitly requested.

---

# Condimentum Standards

Assume the following defaults:

```text
Database Server: condi-sql1

Database:
Condimentum

Backend:
Laravel (PHP 8+)

Database Engine:
Microsoft SQL Server 2017

Frontend:
Bootstrap 5

Tables:
DataTables

Email:
Microsoft Graph API

Architecture:
AJAX First

Authentication:
Microsoft Entra ID Preferred

Logging:
Enabled

Audit Logging:
Enabled
```

---

# Most Important Rule

If information is missing:

DO NOT GUESS.

Do not invent:

- Database tables
- Database columns
- Stored procedures
- Status values
- Approval chains
- Business logic
- Email recipients
- User permissions

Only use explicitly provided requirements or information already present within the project.

When uncertain, ask for clarification before proceeding.

---
End of Condimentum Universal GitHub Copilot Instructions
