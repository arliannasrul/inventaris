<?php

// Fix Laravel routing issue on Vercel: prevent stripping /api from URL
$_SERVER['SCRIPT_NAME'] = '/index.php';

// Forward request to Laravel public/index.php for Vercel Serverless Functions
require __DIR__ . '/../public/index.php';
