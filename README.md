# Ladder Module for Ilch 2.0

Points-based ladder module for Ilch 2.0.

## Features

- Ladder CRUD in admin
- Points model configurable per ladder (`win/draw/loss`)
- Participant/team registration with captain and logo
- Team pages and member profile pages
- Match generation (round-robin), scheduling and status handling
- Result reporting with evidence uploads/links
- Dispute workflow with admin resolution
- Frontend boxes:
  - Upcoming ladder matches
  - Top ladder standings

## Requirements

- Ilch Core `>= 2.2.0`
- PHP `>= 7.3`

## Installation

1. Copy this folder to:
   `application/modules/ladder`
2. Install the module in Ilch admin.
3. Ensure write permissions for:
   `application/modules/ladder/storage`

Database tables are created/updated automatically via `config/config.php`.

## Permissions (ACL)

- `ladder_admin`
- `ladder_manage`
- `ladder_dispute`
- `ladder_team_manage`
- `ladder_report`

## Notes

- Runtime uploads are stored below `storage/`.
- This repository ignores generated files in `storage/`.
