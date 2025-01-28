<?php
/**
 * Plugin Name: Icreative Wordpress Cache Clear
 * Description: Creates a Rest API route to clear Wordpress Cache.
 * Version: 1.0.1
 * Author: Icreative
 */

function icwcc_cache_clear_token_auth($request): bool
{
    return $request->get_header('x-api-key') === API_KEY;
}

add_action('rest_api_init', function () {
    register_rest_route('api/v1', '/icreative/cache/clear', array(
        'methods' => 'POST',
        'callback' => 'icwcc_handle_cache_clear_request',
        'permission_callback' => 'icwcc_cache_clear_token_auth',
    ));
});

function icwcc_purge_w3_total_cache(): void
{
    if (!class_exists('\W3TC\Dispatcher')) {
        return;
    }
    (\W3TC\Dispatcher::component('CacheFlush'))->flush_all();
}

function icwcc_purge_breeze_cache(): void
{
    if (!class_exists('Breeze_Admin')) {
        return;
    }
    (new Breeze_Admin)->breeze_clear_all_cache();
}

function icwcc_purge_cf_cache(): void
{
    if (!class_exists('SW_CLOUDFLARE_PAGECACHE')) {
        return;
    }
    $main_instance = $GLOBALS['sw_cloudflare_pagecache'] ?? null;
    if (!$main_instance) {
        return;
    }
    (new SWCFPC_Cache_Controller(SWCFPC_CACHE_BUSTER, $main_instance))->purge_all();
}

function icwcc_handle_cache_clear_request(): array
{
    icwcc_purge_cf_cache();
    icwcc_purge_breeze_cache();
    icwcc_purge_w3_total_cache();

    return [
        'success' => true,
    ];
}
