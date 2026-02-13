<?php
// Simple health check that doesn't require database
header('Content-Type: application/json');
http_response_code(200);
echo json_encode(['status' => 'ok', 'timestamp' => time()]);
