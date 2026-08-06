CREATE TABLE IF NOT EXISTS admin_login_limits (
    identity_hash TEXT PRIMARY KEY,
    attempts INTEGER NOT NULL DEFAULT 0,
    window_started_at TEXT NOT NULL,
    locked_until TEXT NULL,
    updated_at TEXT NOT NULL
);

CREATE INDEX IF NOT EXISTS idx_admin_login_limits_updated
    ON admin_login_limits(updated_at);
