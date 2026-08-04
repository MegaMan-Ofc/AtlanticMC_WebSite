<?php

declare(strict_types=1);

require_once __DIR__ . '/../includes/bootstrap.php';
require_post();
verify_csrf();

$quantities = $_POST['quantities'] ?? [];
cart_update(is_array($quantities) ? $quantities : []);
flash('success', 'Carrinho atualizado.');
redirect('cart.php');
