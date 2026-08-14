ALTER TABLE orders
    ADD COLUMN tebex_total_cents INT UNSIGNED NULL AFTER provider_checkout_url,
    ADD COLUMN tebex_currency CHAR(3) NULL AFTER tebex_total_cents;
