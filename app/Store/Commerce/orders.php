<?php

declare(strict_types=1);

function create_order(array $recipient, array $summary): array
{
    if ($summary['items'] === [] || (int) $summary['total_cents'] < 0) {
        throw new InvalidArgumentException(t('validation.order_invalid'));
    }

    $username = normalize_minecraft_username(
        (string) ($recipient['username'] ?? ''),
        (string) ($recipient['platform'] ?? '')
    );
    $platform = strtolower((string) $recipient['platform']);
    $pdo = db();
    $pdo->beginTransaction();

    try {
        $token = random_token(24);
        $now = now_sql();
        $statement = $pdo->prepare(
            'INSERT INTO orders
             (public_token, minecraft_name, minecraft_platform, subtotal_cents, discount_cents, total_cents,
              currency, coupon_code, status, provider, created_at, updated_at)
             VALUES
             (:public_token, :minecraft_name, :minecraft_platform, :subtotal_cents, :discount_cents, :total_cents,
              :currency, :coupon_code, :status, :provider, :created_at, :updated_at)'
        );
        $statement->execute([
            'public_token' => $token,
            'minecraft_name' => $username,
            'minecraft_platform' => $platform,
            'subtotal_cents' => (int) $summary['subtotal_cents'],
            'discount_cents' => (int) $summary['discount_cents'],
            'total_cents' => (int) $summary['total_cents'],
            'currency' => (string) $summary['currency'],
            'coupon_code' => $summary['coupon']['code'] ?? null,
            'status' => 'pending',
            'provider' => tebex_is_configured() ? 'tebex' : 'local',
            'created_at' => $now,
            'updated_at' => $now,
        ]);
        $orderId = (int) $pdo->lastInsertId();
        $itemStatement = $pdo->prepare(
            'INSERT INTO order_items
             (order_id, product_id, product_name, unit_price_cents, quantity, line_total_cents, tebex_package_id)
             VALUES (:order_id, :product_id, :product_name, :unit_price_cents, :quantity, :line_total_cents, :tebex_package_id)'
        );

        foreach ($summary['items'] as $item) {
            $product = $item['product'];
            $itemStatement->execute([
                'order_id' => $orderId,
                'product_id' => (int) $product['id'],
                'product_name' => (string) $product['name'],
                'unit_price_cents' => product_effective_price_cents($product),
                'quantity' => (int) $item['quantity'],
                'line_total_cents' => (int) $item['line_total_cents'],
                'tebex_package_id' => $product['tebex_package_id'] ?: null,
            ]);
        }

        $pdo->commit();

        return order_by_token($token) ?? throw new RuntimeException(t('validation.order_reload'));
    } catch (Throwable $error) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }

        throw $error;
    }
}

function update_order_provider(
    int $orderId,
    string $reference,
    string $checkoutUrl,
    int $tebexTotalCents,
    string $tebexCurrency
): void {
    $currency = strtoupper(trim($tebexCurrency));

    if ($tebexTotalCents < 0 || !preg_match('/^[A-Z]{3}$/', $currency)) {
        throw new InvalidArgumentException(t('validation.order_invalid'));
    }

    $statement = db()->prepare(
        'UPDATE orders SET provider = :provider, provider_reference = :reference,
         provider_checkout_url = :checkout_url, tebex_total_cents = :tebex_total_cents,
         tebex_currency = :tebex_currency, status = :status, updated_at = :updated_at WHERE id = :id'
    );
    $statement->execute([
        'provider' => 'tebex',
        'reference' => $reference,
        'checkout_url' => $checkoutUrl,
        'tebex_total_cents' => $tebexTotalCents,
        'tebex_currency' => $currency,
        'status' => 'awaiting_payment',
        'updated_at' => now_sql(),
        'id' => $orderId,
    ]);
}

function order_by_token(string $token): ?array
{
    $statement = db()->prepare('SELECT * FROM orders WHERE public_token = :token');
    $statement->execute(['token' => $token]);
    $order = $statement->fetch();

    if (!is_array($order)) {
        return null;
    }

    $itemStatement = db()->prepare('SELECT * FROM order_items WHERE order_id = :order_id ORDER BY id ASC');
    $itemStatement->execute(['order_id' => $order['id']]);
    $order['items'] = $itemStatement->fetchAll();

    return $order;
}

function mark_order_status_by_token(string $token, string $status, ?string $providerReference = null): void
{
    $allowed = ['pending', 'awaiting_payment', 'checkout_failed', 'paid', 'declined', 'refunded', 'disputed'];

    if (!in_array($status, $allowed, true)) {
        throw new InvalidArgumentException(t('validation.order_status'));
    }

    $pdo = db();
    $pdo->beginTransaction();

    try {
        $select = $pdo->prepare('SELECT id, status, coupon_code, paid_at FROM orders WHERE public_token = :token');
        $select->execute(['token' => $token]);
        $order = $select->fetch();

        if (!is_array($order)) {
            throw new RuntimeException(t('validation.order_not_found'));
        }

        $updatedAt = now_sql();
        $paidAt = $status === 'paid' && empty($order['paid_at']) ? $updatedAt : $order['paid_at'];
        $statement = $pdo->prepare(
            'UPDATE orders SET status = :status,
             provider_reference = COALESCE(:provider_reference, provider_reference),
             paid_at = :paid_at,
             updated_at = :updated_at
             WHERE id = :id'
        );
        $statement->execute([
            'status' => $status,
            'provider_reference' => $providerReference,
            'paid_at' => $paidAt,
            'updated_at' => $updatedAt,
            'id' => $order['id'],
        ]);

        if (
            $status === 'paid'
            && !in_array((string) $order['status'], ['paid', 'refunded', 'disputed'], true)
            && is_string($order['coupon_code'])
            && $order['coupon_code'] !== ''
        ) {
            $couponUpdate = $pdo->prepare(
                'UPDATE coupons SET used_count = used_count + 1, updated_at = :updated_at WHERE code = :code'
            );
            $couponUpdate->execute([
                'updated_at' => now_sql(),
                'code' => $order['coupon_code'],
            ]);
        }

        $pdo->commit();
    } catch (Throwable $error) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }

        throw $error;
    }
}


function process_order_webhook(
    string $provider,
    string $eventId,
    string $eventType,
    string $orderToken,
    string $status,
    ?string $providerReference = null
): bool {
    $allowed = ['paid', 'declined', 'refunded', 'disputed'];

    if (!in_array($status, $allowed, true)) {
        throw new InvalidArgumentException('Invalid webhook order status.');
    }

    $pdo = db();
    $pdo->beginTransaction();

    try {
        $eventStatement = $pdo->prepare(
            'INSERT INTO webhook_events (provider, event_id, event_type, received_at)
             VALUES (:provider, :event_id, :event_type, :received_at)'
        );

        try {
            $eventStatement->execute([
                'provider' => $provider,
                'event_id' => $eventId,
                'event_type' => $eventType,
                'received_at' => now_sql(),
            ]);
        } catch (PDOException $error) {
            $message = strtolower($error->getMessage());

            if (str_contains($message, 'unique') || str_contains($message, 'duplicate')) {
                $pdo->rollBack();
                return false;
            }

            throw $error;
        }

        $select = $pdo->prepare('SELECT id, status, coupon_code, paid_at FROM orders WHERE public_token = :token');
        $select->execute(['token' => $orderToken]);
        $order = $select->fetch();

        if (!is_array($order)) {
            throw new RuntimeException(t('validation.order_not_found'));
        }

        $updatedAt = now_sql();
        $paidAt = $status === 'paid' && empty($order['paid_at']) ? $updatedAt : $order['paid_at'];
        $update = $pdo->prepare(
            'UPDATE orders SET status = :status,
             provider_reference = COALESCE(:provider_reference, provider_reference),
             paid_at = :paid_at,
             updated_at = :updated_at
             WHERE id = :id'
        );
        $update->execute([
            'status' => $status,
            'provider_reference' => $providerReference,
            'paid_at' => $paidAt,
            'updated_at' => $updatedAt,
            'id' => $order['id'],
        ]);

        if (
            $status === 'paid'
            && !in_array((string) $order['status'], ['paid', 'refunded', 'disputed'], true)
            && is_string($order['coupon_code'])
            && $order['coupon_code'] !== ''
        ) {
            $couponUpdate = $pdo->prepare(
                'UPDATE coupons SET used_count = used_count + 1, updated_at = :updated_at WHERE code = :code'
            );
            $couponUpdate->execute([
                'updated_at' => now_sql(),
                'code' => $order['coupon_code'],
            ]);
        }

        $pdo->commit();
        return true;
    } catch (Throwable $error) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }

        throw $error;
    }
}

function record_webhook_event(string $provider, string $eventId, string $eventType): bool
{
    try {
        $statement = db()->prepare(
            'INSERT INTO webhook_events (provider, event_id, event_type, received_at)
             VALUES (:provider, :event_id, :event_type, :received_at)'
        );
        $statement->execute([
            'provider' => $provider,
            'event_id' => $eventId,
            'event_type' => $eventType,
            'received_at' => now_sql(),
        ]);

        return true;
    } catch (PDOException $error) {
        if (str_contains(strtolower($error->getMessage()), 'unique') || str_contains(strtolower($error->getMessage()), 'duplicate')) {
            return false;
        }

        throw $error;
    }
}
