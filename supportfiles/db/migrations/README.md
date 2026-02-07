## iPSK Manager SQL Migrations (future versions)

This folder is reserved for **new** schema updates going forward (post-v6). Existing update scripts (`schemaupdate-v*.sql`) remain manual.

How to add a migration:
- Name files `v<N>__description.sql` (example: `v7__add_new_column.sql`).
- Use tokens instead of hard-coded names where possible:
  - `{{DB_NAME}}` for the database name
  - `{{APP_DB_USERNAME}}` for the application DB user
  - `{{IPSK_DB_USERNAME}}` for the iPSK Manager definer user (optional)
  - `{{ISE_DB_USERNAME}}` for the Cisco ISE ODBC user (optional)
- End each statement with the current delimiter (`;` by default). `DELIMITER` directives are supported for stored procedures.

These migrations are applied automatically (via the admin portal or the CLI runner) only when the target version is higher than the current schema version **and** higher than the existing v6 baseline.
