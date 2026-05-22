<?php

namespace App;

/**
 * Square payment gateway health checks.
 *
 * WooCommerce Square treats "connected" as "access token exists". A revoked or
 * expired token can still pass that check until checkout fails. This module
 * validates the token against Square and alerts admins when payments are down.
 */

const SQUARE_HEALTH_TRANSIENT = 'pbc_square_payment_health';
const SQUARE_HEALTH_TRANSIENT_TTL = 15 * MINUTE_IN_SECONDS;
const SQUARE_HEALTH_ALERT_OPTION = 'pbc_square_payment_health_last_alert';
const SQUARE_HEALTH_CRON_HOOK = 'pbc_square_payment_health_cron';
const SQUARE_GATEWAY_ID = 'square_credit_card';
const SQUARE_CASH_APP_GATEWAY_ID = 'square_cash_app_pay';

/**
 * Run all Square payment health checks.
 *
 * @param bool $force Bypass cached transient result.
 * @return array{ok:bool,checked_at:int,issues:array<int,string>,details:array<string,mixed>}
 */
function square_payment_health_check($force = false)
{
    if (!$force) {
        $cached = get_transient(SQUARE_HEALTH_TRANSIENT);
        if (is_array($cached) && isset($cached['ok'], $cached['checked_at'])) {
            return $cached;
        }
    }

    $result = [
        'ok' => true,
        'checked_at' => time(),
        'issues' => [],
        'details' => [
            'gateway_id' => SQUARE_GATEWAY_ID,
        ],
    ];

    if (!function_exists('WC') || !WC()) {
        $result['ok'] = false;
        $result['issues'][] = 'WooCommerce is not available.';
        square_payment_health_store($result);
        return $result;
    }

    if (!function_exists('wc_square')) {
        $result['ok'] = false;
        $result['issues'][] = 'WooCommerce Square plugin is not active.';
        square_payment_health_store($result);
        return $result;
    }

    $gateways = WC()->payment_gateways()->payment_gateways();
    if (empty($gateways[SQUARE_GATEWAY_ID])) {
        $result['ok'] = false;
        $result['issues'][] = 'Square credit card gateway is not registered.';
        square_payment_health_store($result);
        return $result;
    }

    /** @var \WC_Payment_Gateway $gateway */
    $gateway = $gateways[SQUARE_GATEWAY_ID];
    $result['details']['gateway_enabled'] = ($gateway->enabled === 'yes');

    if ($gateway->enabled !== 'yes') {
        $result['ok'] = false;
        $result['issues'][] = 'Square gateway is disabled in WooCommerce settings.';
        square_payment_health_store($result);
        return $result;
    }

    $settings = wc_square()->get_settings_handler();
    $isConnected = (bool) $settings->is_connected();
    $locationId = (string) $settings->get_location_id();
    $isSandbox = (bool) $settings->is_sandbox();

    $result['details']['is_connected'] = $isConnected;
    $result['details']['location_id'] = $locationId;
    $result['details']['environment'] = $isSandbox ? 'sandbox' : 'production';

    if (!$isConnected) {
        $result['ok'] = false;
        $result['issues'][] = 'Square account is not connected (no access token stored).';
        square_payment_health_store($result);
        return $result;
    }

    if ($locationId === '') {
        $result['ok'] = false;
        $result['issues'][] = 'Square location ID is not configured.';
        square_payment_health_store($result);
        return $result;
    }

    if (method_exists($gateway, 'is_available') && !$gateway->is_available()) {
        $result['ok'] = false;
        $result['issues'][] = 'Square gateway reports unavailable at checkout.';
    }

    $tokenCheck = square_payment_health_validate_token(
        (string) $settings->get_access_token(),
        $isSandbox
    );
    $result['details']['token_check'] = $tokenCheck;

    if (!$tokenCheck['ok']) {
        $result['ok'] = false;
        $result['issues'][] = $tokenCheck['message'];
    }

    square_payment_health_store($result);

    if (!$result['ok']) {
        square_payment_health_maybe_alert($result);
    } else {
        delete_option(SQUARE_HEALTH_ALERT_OPTION);
    }

    return $result;
}

/**
 * Validate Square OAuth token with Square token status endpoint.
 *
 * @param string $accessToken
 * @param bool   $sandbox
 * @return array{ok:bool,message:string,http_code:int|null,merchant_id:string|null}
 */
function square_payment_health_validate_token($accessToken, $sandbox)
{
    if ($accessToken === '') {
        return [
            'ok' => false,
            'message' => 'Square access token is missing.',
            'http_code' => null,
            'merchant_id' => null,
        ];
    }

    $url = $sandbox
        ? 'https://connect.squareupsandbox.com/oauth2/token/status'
        : 'https://connect.squareup.com/oauth2/token/status';

    $response = wp_remote_post($url, [
        'timeout' => 15,
        'headers' => [
            'Authorization' => 'Bearer ' . $accessToken,
            'Content-Type' => 'application/json',
            'Square-Version' => '2024-01-18',
        ],
    ]);

    if (is_wp_error($response)) {
        return [
            'ok' => false,
            'message' => 'Could not reach Square API: ' . $response->get_error_message(),
            'http_code' => null,
            'merchant_id' => null,
        ];
    }

    $httpCode = (int) wp_remote_retrieve_response_code($response);
    $body = json_decode((string) wp_remote_retrieve_body($response), true);

    if ($httpCode >= 200 && $httpCode < 300) {
        return [
            'ok' => true,
            'message' => 'Square token is valid.',
            'http_code' => $httpCode,
            'merchant_id' => isset($body['merchant_id']) ? (string) $body['merchant_id'] : null,
        ];
    }

    if ($httpCode === 401 || $httpCode === 403) {
        return [
            'ok' => false,
            'message' => 'Square access token is invalid or expired. Reconnect Square in WooCommerce settings.',
            'http_code' => $httpCode,
            'merchant_id' => null,
        ];
    }

    $apiMessage = '';
    if (is_array($body) && !empty($body['errors'][0]['detail'])) {
        $apiMessage = (string) $body['errors'][0]['detail'];
    } elseif (is_array($body) && !empty($body['errors'][0]['code'])) {
        $apiMessage = (string) $body['errors'][0]['code'];
    }

    return [
        'ok' => false,
        'message' => $apiMessage !== ''
            ? 'Square token validation failed: ' . $apiMessage
            : 'Square token validation failed with HTTP ' . $httpCode . '.',
        'http_code' => $httpCode,
        'merchant_id' => null,
    ];
}

/**
 * @param array{ok:bool,checked_at:int,issues:array<int,string>,details:array<string,mixed>} $result
 */
function square_payment_health_store(array $result)
{
    set_transient(SQUARE_HEALTH_TRANSIENT, $result, SQUARE_HEALTH_TRANSIENT_TTL);
}

/**
 * @return array{ok:bool,checked_at:int,issues:array<int,string>,details:array<string,mixed>}|null
 */
function square_payment_health_get_cached()
{
    $cached = get_transient(SQUARE_HEALTH_TRANSIENT);
    return is_array($cached) ? $cached : null;
}

/**
 * @param array{ok:bool,checked_at:int,issues:array<int,string>,details:array<string,mixed>} $result
 */
function square_payment_health_maybe_alert(array $result)
{
    $lastAlert = (int) get_option(SQUARE_HEALTH_ALERT_OPTION, 0);
    if ($lastAlert > 0 && (time() - $lastAlert) < DAY_IN_SECONDS) {
        return;
    }

    $adminEmail = (string) get_option('admin_email');
    if ($adminEmail === '') {
        return;
    }

    $subject = sprintf('[%s] Square payments health check failed', wp_specialchars_decode(get_bloginfo('name'), ENT_QUOTES));
    $body = "Square payment processing appears unavailable.\n\n";
    $body .= "Site: " . home_url('/') . "\n";
    $body .= "Checked: " . gmdate('Y-m-d H:i:s', $result['checked_at']) . " UTC\n\n";
    $body .= "Issues:\n- " . implode("\n- ", $result['issues']) . "\n\n";
    $body .= "Action: WooCommerce > Settings > Payments > Square and reconnect the account.\n";

    wp_mail($adminEmail, $subject, $body);
    update_option(SQUARE_HEALTH_ALERT_OPTION, time(), false);
}

/**
 * Remove Square gateways when health check fails.
 *
 * @param array<string,\WC_Payment_Gateway> $gateways
 * @return array<string,\WC_Payment_Gateway>
 */
function square_payment_health_filter_gateways(array $gateways)
{
    $health = square_payment_health_get_cached();
    if (!$health || !empty($health['ok'])) {
        return $gateways;
    }

    unset($gateways[SQUARE_GATEWAY_ID], $gateways[SQUARE_CASH_APP_GATEWAY_ID]);

    return $gateways;
}

add_filter('woocommerce_available_payment_gateways', __NAMESPACE__ . '\\square_payment_health_filter_gateways');

add_action('template_redirect', function () {
    if (!function_exists('is_checkout') || !is_checkout() || is_wc_endpoint_url('order-received')) {
        return;
    }

    if (!square_payment_health_get_cached()) {
        square_payment_health_check(false);
    }
}, 5);

add_action('woocommerce_before_checkout_form', function () {
    $health = square_payment_health_get_cached() ?: square_payment_health_check(false);

    if (!empty($health['ok'])) {
        return;
    }

    wc_print_notice(
        __('Online card payments are temporarily unavailable. Please contact us to complete your order.', 'sage'),
        'error'
    );
}, 5);

add_action('woocommerce_cart_has_errors', function () {
    if (!is_checkout()) {
        return;
    }

    $health = square_payment_health_get_cached() ?: square_payment_health_check(false);
    if (!empty($health['ok'])) {
        return;
    }

    wc_add_notice(
        __('Online card payments are temporarily unavailable. Please contact us to complete your order.', 'sage'),
        'error'
    );
});

add_action('admin_notices', function () {
    if (!current_user_can('manage_woocommerce')) {
        return;
    }

    $health = square_payment_health_get_cached() ?: square_payment_health_check(false);
    if (empty($health) || !empty($health['ok'])) {
        return;
    }

    $issues = implode(' ', array_map('esc_html', $health['issues']));
    $settingsUrl = admin_url('admin.php?page=wc-settings&tab=checkout&section=square_credit_card');

    echo '<div class="notice notice-error"><p><strong>Square payments unavailable:</strong> '
        . $issues
        . ' <a href="' . esc_url($settingsUrl) . '">Open Square settings</a>.</p></div>';
});

add_action('init', function () {
    if (!wp_next_scheduled(SQUARE_HEALTH_CRON_HOOK)) {
        wp_schedule_event(time() + MINUTE_IN_SECONDS, 'hourly', SQUARE_HEALTH_CRON_HOOK);
    }
});

add_action(SQUARE_HEALTH_CRON_HOOK, function () {
    square_payment_health_check(true);
});

add_action('rest_api_init', function () {
    register_rest_route('pbc/v1', '/payment-health', [
        'methods' => 'GET',
        'permission_callback' => function ($request) {
            $expected = env('PAYMENT_HEALTH_TOKEN');
            if (empty($expected)) {
                return false;
            }

            $provided = $request->get_header('x-pbc-health-token');
            return is_string($provided) && hash_equals((string) $expected, $provided);
        },
        'callback' => function () {
            $health = square_payment_health_check(true);

            return new \WP_REST_Response([
                'ok' => (bool) $health['ok'],
                'checked_at' => gmdate('c', $health['checked_at']),
                'issues' => $health['issues'],
                'details' => $health['details'],
            ], !empty($health['ok']) ? 200 : 503);
        },
    ]);
});

add_action('switch_theme', function () {
    wp_clear_scheduled_hook(SQUARE_HEALTH_CRON_HOOK);
});
