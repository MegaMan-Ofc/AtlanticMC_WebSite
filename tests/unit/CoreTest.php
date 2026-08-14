<?php

declare(strict_types=1);

$assert(parse_environment_line('VALUE="abc" # note') === ['VALUE', 'abc'], 'Quoted environment values ignore trailing comments.');
$assert(parse_environment_line('VALUE=abc # note') === ['VALUE', 'abc'], 'Unquoted environment values ignore whitespace comments.');
$assert(parse_environment_line('INVALID-NAME=value') === null, 'Invalid environment names are rejected.');
$assert(parse_money_to_cents('14,99') === 1499, 'Comma money values are converted to cents.');
$assert(parse_money_to_cents('14.9') === 1490, 'Single-decimal money values are normalized.');
$throws(static fn () => parse_money_to_cents('-1'), 'Negative money values are rejected.');
$assert(safe_return_path('https://example.com', 'cart') === 'cart', 'External return URLs are rejected.');
$assert(safe_return_path('/ranks.php?x=1', '') === 'ranks?x=1', 'Legacy public return paths are normalized.');
$assert(ip_matches_network('192.168.1.10', '192.168.1.0/24'), 'IPv4 CIDR matching works.');
$assert(!ip_matches_network('192.168.2.10', '192.168.1.0/24'), 'IPv4 CIDR mismatch is rejected.');
$assert(csrf_is_valid('token', 'token'), 'Valid CSRF tokens pass.');
$assert(!csrf_is_valid('wrong', 'token'), 'Invalid CSRF tokens fail.');
$assert(!admin_is_authenticated(), 'An empty session is not an authenticated administrator.');
$assert(configuration_errors() === [], 'The isolated test configuration is valid.');
$assert(
    normalize_minecraft_username('Java_User', 'java') === 'Java_User'
        && normalize_minecraft_username('Bed Rock', 'bedrock') === 'Bed Rock'
        && minecraft_server_username('Bed Rock', 'bedrock') === '.Bed_Rock',
    'Java and Bedrock recipients normalize to the expected server usernames.'
);
$throws(
    static fn () => normalize_minecraft_username('ab', 'java'),
    'Invalid Java usernames are rejected.'
);
$throws(
    static fn () => normalize_minecraft_username('Bed-Rock!', 'bedrock'),
    'Invalid Bedrock Gamertags are rejected.'
);
