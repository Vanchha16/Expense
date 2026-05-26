<?php
/**
 * Vercel PHP serverless entry point for Laravel.
 * All HTTP requests are routed here via vercel.json.
 */

// Tell Laravel we're behind an HTTPS reverse proxy (Vercel's edge network)
$_SERVER['HTTPS']       = 'on';
$_SERVER['SERVER_PORT'] = '443';

// Boot Laravel from the public directory
require __DIR__ . '/../public/index.php';
