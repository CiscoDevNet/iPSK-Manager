-- Username equality follows the column collation (case-insensitive in the supplied schema).
-- Duplicate existing names cause this statement to fail without dropping any accounts.
-- Resolve the conflicting usernames explicitly, then retry the migration.
ALTER TABLE internalUsers ADD UNIQUE KEY unique_internal_username (userName);
