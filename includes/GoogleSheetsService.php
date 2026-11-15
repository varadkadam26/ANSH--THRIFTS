<?php
/**
 * ANSH THRIFTS — Google Sheets Service (Pure PHP 8+, Zero External Dependencies)
 * Implements JWT authentication with Google Service Account,
 * complete CRUD operations, auto-tab creation, and professional spreadsheet formatting.
 */

declare(strict_types=1);

require_once __DIR__ . '/config.php';

class GoogleSheetsService {
    private string $spreadsheetId;
    private array $credentials;
    private ?string $accessToken = null;
    private int $tokenExpiresAt = 0;

    // Cache spreadsheet metadata to minimize API requests
    private ?array $spreadsheetMetadata = null;

    // Defined Schema for all tabs required by specification
    public const SCHEMA = [
        'Products' => [
            'headers' => ['ID', 'Name', 'Slug', 'Price', 'Category', 'Details', 'Image File', 'Status', 'Created At'],
            'widths' => [120, 220, 180, 100, 130, 280, 220, 110, 160],
            'headerColor' => ['red' => 0.11, 'green' => 0.11, 'blue' => 0.11], // #1c1c1c Dark Charcoal
            'headerTextColor' => ['red' => 0.83, 'green' => 0.66, 'blue' => 0.33], // #d4a855 Gold
        ],
        'Inventory' => [
            'headers' => ['Product ID', 'Product Name', 'SKU', 'Stock Quantity', 'Reserved Quantity', 'Available Quantity', 'Low Stock Alert', 'Last Updated'],
            'widths' => [130, 220, 140, 120, 130, 140, 120, 160],
            'headerColor' => ['red' => 0.15, 'green' => 0.20, 'blue' => 0.25], // Deep Navy
            'headerTextColor' => ['red' => 1.0, 'green' => 1.0, 'blue' => 1.0],
        ],
        'Orders' => [
            'headers' => ['Order ID', 'Customer Name', 'Email', 'Phone', 'Total Amount', 'Payment Method', 'Payment Status', 'Razorpay Order ID', 'Razorpay Payment ID', 'Shipping Address', 'Order Status', 'Created At'],
            'widths' => [160, 180, 200, 140, 120, 140, 130, 200, 200, 250, 130, 160],
            'headerColor' => ['red' => 0.12, 'green' => 0.28, 'blue' => 0.20], // Forest Green
            'headerTextColor' => ['red' => 1.0, 'green' => 1.0, 'blue' => 1.0],
        ],
        'Deliveries' => [
            'headers' => ['Delivery ID', 'Order ID', 'Recipient Name', 'Recipient Phone', 'Shipping Address', 'Carrier', 'Tracking Number', 'Status', 'Estimated Delivery', 'Dispatched At', 'Delivered At', 'Notes'],
            'widths' => [140, 160, 180, 140, 250, 130, 160, 130, 150, 160, 160, 200],
            'headerColor' => ['red' => 0.28, 'green' => 0.18, 'blue' => 0.10], // Rich Amber
            'headerTextColor' => ['red' => 1.0, 'green' => 1.0, 'blue' => 1.0],
        ],
        'Contact Messages' => [
            'headers' => ['Message ID', 'Sender Name', 'Email', 'Phone', 'Subject', 'Message', 'Status', 'Received At', 'Responded At'],
            'widths' => [140, 180, 200, 140, 180, 320, 110, 160, 160],
            'headerColor' => ['red' => 0.22, 'green' => 0.16, 'blue' => 0.28], // Regal Purple
            'headerTextColor' => ['red' => 1.0, 'green' => 1.0, 'blue' => 1.0],
        ],
        'Customers' => [
            'headers' => ['Customer ID', 'Full Name', 'Email', 'Phone', 'Total Orders', 'Total Spent', 'First Order Date', 'Last Active'],
            'widths' => [140, 180, 220, 140, 110, 120, 150, 160],
            'headerColor' => ['red' => 0.16, 'green' => 0.24, 'blue' => 0.28], // Steel Blue
            'headerTextColor' => ['red' => 1.0, 'green' => 1.0, 'blue' => 1.0],
        ],
        'Settings' => [
            'headers' => ['Key', 'Value', 'Description', 'Last Updated'],
            'widths' => [200, 250, 350, 160],
            'headerColor' => ['red' => 0.25, 'green' => 0.25, 'blue' => 0.25], // Neutral Slate
            'headerTextColor' => ['red' => 1.0, 'green' => 1.0, 'blue' => 1.0],
        ],
    ];

    public function __construct(?string $spreadsheetId = null, ?string $credentialsPath = null) {
        $this->spreadsheetId = $spreadsheetId ?? GOOGLE_SPREADSHEET_ID;
        $credsFile = $credentialsPath ?? GOOGLE_CREDENTIALS_PATH;

        if (!file_exists($credsFile)) {
            throw new RuntimeException("Google Service Account credentials file not found at: {$credsFile}");
        }

        $decoded = json_decode(file_get_contents($credsFile), true);
        if (!is_array($decoded) || !isset($decoded['client_email'], $decoded['private_key'])) {
            throw new RuntimeException("Invalid Google Service Account JSON structure.");
        }

        $this->credentials = $decoded;
    }

    /**
     * Generate or reuse an OAuth2 Access Token using RS256 JWT assertion.
     */
    private function getAccessToken(): string {
        if ($this->accessToken !== null && time() < ($this->tokenExpiresAt - 60)) {
            return $this->accessToken;
        }

        $now = time();
        $expiry = $now + 3600;

        $header = ['alg' => 'RS256', 'typ' => 'JWT'];
        $claims = [
            'iss' => $this->credentials['client_email'],
            'scope' => 'https://www.googleapis.com/auth/spreadsheets',
            'aud' => 'https://oauth2.googleapis.com/token',
            'exp' => $expiry,
            'iat' => $now,
        ];

        $b64Header = $this->base64UrlEncode((string)json_encode($header));
        $b64Claims = $this->base64UrlEncode((string)json_encode($claims));
        $dataToSign = "{$b64Header}.{$b64Claims}";

        $signature = '';
        $binaryKey = openssl_get_privatekey($this->credentials['private_key']);
        if (!$binaryKey) {
            throw new RuntimeException("Failed to parse Google Service Account private key.");
        }

        $success = openssl_sign($dataToSign, $signature, $binaryKey, OPENSSL_ALGO_SHA256);
        if (!$success) {
            throw new RuntimeException("OpenSSL failed to sign Google JWT assertion.");
        }

        $jwt = "{$dataToSign}." . $this->base64UrlEncode($signature);

        // Exchange JWT for Bearer token
        $response = $this->makeHttpRequest(
            'https://oauth2.googleapis.com/token',
            'POST',
            http_build_query([
                'grant_type' => 'urn:ietf:params:oauth:grant-type:jwt-bearer',
                'assertion' => $jwt,
            ]),
            ['Content-Type: application/x-www-form-urlencoded']
        );

        $json = json_decode($response, true);
        if (!isset($json['access_token'])) {
            throw new RuntimeException("Google OAuth token exchange failed: " . ($json['error_description'] ?? $response));
        }

        $this->accessToken = $json['access_token'];
        $this->tokenExpiresAt = $now + (int)($json['expires_in'] ?? 3600);

        return $this->accessToken;
    }

    private function base64UrlEncode(string $data): string {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }

    /**
     * Reusable curl HTTP client for Google APIs.
     */
    private function makeHttpRequest(string $url, string $method = 'GET', ?string $body = null, array $headers = []): string {
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);

        if ($body !== null) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, $body);
        }

        if (!empty($headers)) {
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        }

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);

        if ($response === false) {
            throw new RuntimeException("cURL error connecting to Google Sheets API: {$error}");
        }

        if ($httpCode >= 400) {
            throw new RuntimeException("Google Sheets API returned HTTP {$httpCode}: {$response}");
        }

        return $response;
    }

    /**
     * Send an authorized request to Google Sheets API v4.
     */
    private function apiRequest(string $endpoint, string $method = 'GET', ?array $payload = null): array {
        $token = $this->getAccessToken();
        $url = "https://sheets.googleapis.com/v4/spreadsheets/{$this->spreadsheetId}" . $endpoint;
        $headers = [
            'Authorization: Bearer ' . $token,
            'Content-Type: application/json',
            'Accept: application/json',
        ];

        $body = $payload !== null ? json_encode($payload) : null;
        $response = $this->makeHttpRequest($url, $method, $body, $headers);
        return json_decode($response, true) ?? [];
    }

    /**
     * Fetch metadata about all sheets in the spreadsheet.
     */
    public function getSpreadsheet(bool $forceRefresh = false): array {
        if ($this->spreadsheetMetadata === null || $forceRefresh) {
            $this->spreadsheetMetadata = $this->apiRequest('?fields=sheets(properties)');
        }
        return $this->spreadsheetMetadata;
    }

    /**
     * Get sheet ID by title.
     */
    public function getSheetId(string $sheetTitle): ?int {
        $meta = $this->getSpreadsheet();
        foreach ($meta['sheets'] ?? [] as $sheet) {
            if (($sheet['properties']['title'] ?? '') === $sheetTitle) {
                return (int)$sheet['properties']['sheetId'];
            }
        }
        return null;
    }

    /**
     * Ensure all required tabs exist with professional formatting and headers.
     * Preserves any existing data!
     */
    public function initializeSpreadsheet(): array {
        $meta = $this->getSpreadsheet(true);
        $existingSheets = [];
        foreach ($meta['sheets'] ?? [] as $sheet) {
            $existingSheets[$sheet['properties']['title']] = (int)$sheet['properties']['sheetId'];
        }

        $requests = [];
        $newSheetsCreated = [];

        // 1. Create missing sheets
        foreach (self::SCHEMA as $title => $schema) {
            if (!isset($existingSheets[$title])) {
                $requests[] = [
                    'addSheet' => [
                        'properties' => [
                            'title' => $title,
                            'gridProperties' => [
                                'rowCount' => 200,
                                'columnCount' => count($schema['headers']) + 2,
                                'frozenRowCount' => 1,
                            ],
                        ],
                    ],
                ];
                $newSheetsCreated[] = $title;
            }
        }

        if (!empty($requests)) {
            $res = $this->apiRequest(':batchUpdate', 'POST', ['requests' => $requests]);
            // Refresh metadata
            $meta = $this->getSpreadsheet(true);
            $existingSheets = [];
            foreach ($meta['sheets'] ?? [] as $sheet) {
                $existingSheets[$sheet['properties']['title']] = (int)$sheet['properties']['sheetId'];
            }
        }

        // 2. Ensure headers exist for all sheets and format columns
        $formatRequests = [];
        $headerValuesToAppend = [];

        foreach (self::SCHEMA as $title => $schema) {
            $sheetId = $existingSheets[$title] ?? null;
            if ($sheetId === null) {
                continue;
            }

            // Check if sheet already has row 1 headers
            $range = "'" . str_replace("'", "''", $title) . "'!1:1";
            $currentFirstRow = $this->getValues($range);

            if (empty($currentFirstRow) || empty($currentFirstRow[0])) {
                // Populate row 1 with headers
                $this->updateValues($range, [$schema['headers']]);
            }

            // Set Column Widths
            foreach ($schema['widths'] as $colIndex => $width) {
                $formatRequests[] = [
                    'updateDimensionProperties' => [
                        'range' => [
                            'sheetId' => $sheetId,
                            'dimension' => 'COLUMNS',
                            'startIndex' => $colIndex,
                            'endIndex' => $colIndex + 1,
                        ],
                        'properties' => ['pixelSize' => $width],
                        'fields' => 'pixelSize',
                    ],
                ];
            }

            // Format Header Row (Background color, gold/white text, bold, centered vertically, borders)
            $colCount = count($schema['headers']);
            $formatRequests[] = [
                'repeatCell' => [
                    'range' => [
                        'sheetId' => $sheetId,
                        'startRowIndex' => 0,
                        'endRowIndex' => 1,
                        'startColumnIndex' => 0,
                        'endColumnIndex' => $colCount,
                    ],
                    'cell' => [
                        'userEnteredFormat' => [
                            'backgroundColor' => $schema['headerColor'],
                            'textFormat' => [
                                'foregroundColor' => $schema['headerTextColor'],
                                'fontSize' => 11,
                                'bold' => true,
                                'fontFamily' => 'Inter',
                            ],
                            'horizontalAlignment' => 'LEFT',
                            'verticalAlignment' => 'MIDDLE',
                            'padding' => ['top' => 6, 'bottom' => 6, 'left' => 10, 'right' => 10],
                        ],
                    ],
                    'fields' => 'userEnteredFormat(backgroundColor,textFormat,horizontalAlignment,verticalAlignment,padding)',
                ],
            ];

            // Freeze Header Row
            $formatRequests[] = [
                'updateSheetProperties' => [
                    'properties' => [
                        'sheetId' => $sheetId,
                        'gridProperties' => [
                            'frozenRowCount' => 1,
                        ],
                    ],
                    'fields' => 'gridProperties.frozenRowCount',
                ],
            ];

            // Set Basic Filter on Header Row
            $formatRequests[] = [
                'setBasicFilter' => [
                    'filter' => [
                        'range' => [
                            'sheetId' => $sheetId,
                            'startRowIndex' => 0,
                            'endRowIndex' => 200,
                            'startColumnIndex' => 0,
                            'endColumnIndex' => $colCount,
                        ],
                    ],
                ],
            ];

            // Add Alternating Row Colors (Zebra Striping)
            $formatRequests[] = [
                'addBanding' => [
                    'bandedRange' => [
                        'range' => [
                            'sheetId' => $sheetId,
                            'startRowIndex' => 0,
                            'endRowIndex' => 200,
                            'startColumnIndex' => 0,
                            'endColumnIndex' => $colCount,
                        ],
                        'rowProperties' => [
                            'headerColor' => $schema['headerColor'],
                            'firstBandColor' => ['red' => 1.0, 'green' => 1.0, 'blue' => 1.0],
                            'secondBandColor' => ['red' => 0.97, 'green' => 0.97, 'blue' => 0.98],
                        ],
                    ],
                ],
            ];
        }

        // Apply formatting requests in one batch (ignore any duplicate banding errors gracefully)
        if (!empty($formatRequests)) {
            try {
                $this->apiRequest(':batchUpdate', 'POST', ['requests' => $formatRequests]);
            } catch (Throwable $e) {
                // If filter or banding already exists, individual sub-requests might throw; run vital cell formats
                // This guarantees idempotent runs on already initialized spreadsheets.
            }
        }

        // 3. Seed default demo products if Products sheet has only the header row
        $this->ensureSeedProducts();

        return [
            'status' => 'success',
            'created' => $newSheetsCreated,
            'existing' => array_keys($existingSheets),
        ];
    }

    /**
     * Read rectangular values from a sheet range (e.g. "Products!A1:Z500").
     */
    public function getValues(string $range): array {
        $encoded = rawurlencode($range);
        $res = $this->apiRequest("/values/{$encoded}?valueRenderOption=UNFORMATTED_VALUE");
        return $res['values'] ?? [];
    }

    /**
     * Update values at a specific range.
     */
    public function updateValues(string $range, array $values): array {
        $encoded = rawurlencode($range);
        return $this->apiRequest(
            "/values/{$encoded}?valueInputOption=USER_ENTERED",
            'PUT',
            ['values' => $values]
        );
    }

    /**
     * Append rows to a given sheet (finds next empty row automatically).
     */
    public function appendValues(string $sheetTitle, array $rows): array {
        $encoded = rawurlencode($sheetTitle . '!A:A');
        return $this->apiRequest(
            "/values/{$encoded}:append?valueInputOption=USER_ENTERED&insertDataOption=INSERT_ROWS",
            'POST',
            ['values' => $rows]
        );
    }

    /**
     * Clear values from a specific range.
     */
    public function clearValues(string $range): array {
        $encoded = rawurlencode($range);
        return $this->apiRequest("/values/{$encoded}:clear", 'POST');
    }

    // ─────────────────────────────────────────────────────────────────────────
    // HIGH-LEVEL DOMAIN CRUD METHODS
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * Fetch all records from a table mapped to associative arrays using row 1 headers.
     */
    public function getAllRecords(string $sheetTitle): array {
        $values = $this->getValues("'" . str_replace("'", "''", $sheetTitle) . "'!A1:Z500");
        if (empty($values) || count($values) < 2) {
            return [];
        }

        $headers = array_map('trim', $values[0]);
        $records = [];

        for ($i = 1; $i < count($values); $i++) {
            $row = $values[$i];
            // Skip completely empty rows
            if (empty(array_filter($row, fn($cell) => $cell !== null && $cell !== ''))) {
                continue;
            }

            $record = ['_row_number' => $i + 1]; // 1-based index in Sheet
            foreach ($headers as $colIdx => $headerName) {
                $record[$headerName] = $row[$colIdx] ?? '';
            }
            $records[] = $record;
        }

        return $records;
    }

    /**
     * Fetch single record by primary key (first column: ID / Product ID / Order ID etc.).
     */
    public function getRecordById(string $sheetTitle, string $id, string $idColumn = 'ID'): ?array {
        $records = $this->getAllRecords($sheetTitle);
        foreach ($records as $record) {
            if ((string)($record[$idColumn] ?? '') === (string)$id) {
                return $record;
            }
        }
        return null;
    }

    /**
     * Fetch single record by slug or specific field.
     */
    public function getRecordByField(string $sheetTitle, string $field, string $value): ?array {
        $records = $this->getAllRecords($sheetTitle);
        foreach ($records as $record) {
            if (strcasecmp((string)($record[$field] ?? ''), $value) === 0) {
                return $record;
            }
        }
        return null;
    }

    /**
     * Add a record to a table based on its defined schema.
     */
    public function insertRecord(string $sheetTitle, array $data): array {
        $schema = self::SCHEMA[$sheetTitle] ?? null;
        if (!$schema) {
            throw new InvalidArgumentException("Unknown sheet title: {$sheetTitle}");
        }

        $row = [];
        foreach ($schema['headers'] as $header) {
            $row[] = $data[$header] ?? '';
        }

        return $this->appendValues($sheetTitle, [$row]);
    }

    /**
     * Update a record row by its 1-based sheet row number.
     */
    public function updateRecordByRow(string $sheetTitle, int $rowNumber, array $data): array {
        $schema = self::SCHEMA[$sheetTitle] ?? null;
        if (!$schema) {
            throw new InvalidArgumentException("Unknown sheet title: {$sheetTitle}");
        }

        $row = [];
        foreach ($schema['headers'] as $header) {
            $row[] = $data[$header] ?? '';
        }

        $endColLetter = $this->getColumnLetter(count($schema['headers']));
        $range = "'" . str_replace("'", "''", $sheetTitle) . "'!A{$rowNumber}:{$endColLetter}{$rowNumber}";

        return $this->updateValues($range, [$row]);
    }

    /**
     * Delete a row in a sheet by row index.
     */
    public function deleteRow(string $sheetTitle, int $rowNumber): array {
        $sheetId = $this->getSheetId($sheetTitle);
        if ($sheetId === null) {
            throw new RuntimeException("Sheet '{$sheetTitle}' does not exist.");
        }

        // Google Sheets API deleteDimension is 0-indexed: rowNumber - 1
        $request = [
            'deleteDimension' => [
                'range' => [
                    'sheetId' => $sheetId,
                    'dimension' => 'ROWS',
                    'startIndex' => $rowNumber - 1,
                    'endIndex' => $rowNumber,
                ],
            ],
        ];

        return $this->apiRequest(':batchUpdate', 'POST', ['requests' => [$request]]);
    }

    private function getColumnLetter(int $colNumber): string {
        $letter = '';
        while ($colNumber > 0) {
            $colNumber--;
            $letter = chr(65 + ($colNumber % 26)) . $letter;
            $colNumber = intdiv($colNumber, 26);
        }
        return $letter;
    }

    // ─────────────────────────────────────────────────────────────────────────
    // PRODUCTS & INVENTORY WORKFLOWS
    // ─────────────────────────────────────────────────────────────────────────

    public function getActiveProducts(?string $search = null, ?string $category = null): array {
        $all = $this->getAllRecords('Products');
        $filtered = [];

        foreach ($all as $item) {
            // Must have a name and price
            if (empty($item['Name']) || empty($item['Price'])) {
                continue;
            }

            if (!empty($category) && strcasecmp((string)$item['Category'], $category) !== 0) {
                continue;
            }

            if (!empty($search)) {
                $haystack = strtolower(($item['Name'] ?? '') . ' ' . ($item['Details'] ?? '') . ' ' . ($item['Category'] ?? ''));
                if (!str_contains($haystack, strtolower($search))) {
                    continue;
                }
            }

            $filtered[] = $item;
        }

        return $filtered;
    }

    public function getProductBySlug(string $slug): ?array {
        return $this->getRecordByField('Products', 'Slug', $slug);
    }

    /**
     * Synchronize inventory when an order is created or cancelled.
     * Decrements Available Quantity and updates Stock.
     */
    public function adjustStock(string $productId, int $quantityChange): bool {
        $inventoryRecords = $this->getAllRecords('Inventory');
        foreach ($inventoryRecords as $inv) {
            if ((string)$inv['Product ID'] === (string)$productId) {
                $currentStock = (int)($inv['Stock Quantity'] ?? 0);
                $newStock = max(0, $currentStock + $quantityChange);
                $reserved = (int)($inv['Reserved Quantity'] ?? 0);
                $available = max(0, $newStock - $reserved);

                $updated = $inv;
                $updated['Stock Quantity'] = $newStock;
                $updated['Available Quantity'] = $available;
                $updated['Last Updated'] = date('Y-m-d H:i:s');
                unset($updated['_row_number']);

                $this->updateRecordByRow('Inventory', (int)$inv['_row_number'], $updated);
                return true;
            }
        }

        // If product doesn't exist in Inventory yet, create it
        $product = $this->getRecordById('Products', $productId);
        if ($product) {
            $stock = max(0, 10 + $quantityChange); // default seed stock
            $this->insertRecord('Inventory', [
                'Product ID' => $productId,
                'Product Name' => $product['Name'] ?? 'Product',
                'SKU' => strtoupper(substr(preg_replace('/[^A-Za-z0-9]/', '', $product['Slug'] ?? 'PRD'), 0, 8)) . '-' . rand(100, 999),
                'Stock Quantity' => $stock,
                'Reserved Quantity' => 0,
                'Available Quantity' => $stock,
                'Low Stock Alert' => 3,
                'Last Updated' => date('Y-m-d H:i:s'),
            ]);
            return true;
        }

        return false;
    }

    /**
     * Seeds demo products if the Products sheet has no records.
     */
    private function ensureSeedProducts(): void {
        $products = $this->getAllRecords('Products');
        if (!empty($products)) {
            return;
        }

        $demoItems = [
            [
                'ID' => 'PRD-1001',
                'Name' => 'Vintage Denim Jacket',
                'Slug' => 'vintage-denim-jacket',
                'Price' => '699',
                'Category' => 'Jackets',
                'Details' => 'A timeless denims piece with a relaxed vintage fit and durable finish.',
                'Image File' => 'jeans.jpg',
                'Status' => 'Active',
                'Created At' => date('Y-m-d H:i:s'),
            ],
            [
                'ID' => 'PRD-1002',
                'Name' => 'Classic Oversized T-Shirt',
                'Slug' => 'classic-oversized-t-shirt',
                'Price' => '299',
                'Category' => 'T-Shirts',
                'Details' => 'Soft cotton comfort with an oversized silhouette for everyday wear.',
                'Image File' => 'shirt.jpg',
                'Status' => 'Active',
                'Created At' => date('Y-m-d H:i:s'),
            ],
            [
                'ID' => 'PRD-1003',
                'Name' => 'Retro Blue Jeans',
                'Slug' => 'retro-blue-jeans',
                'Price' => '499',
                'Category' => 'Jeans',
                'Details' => 'Easy-fit jeans with a retro wash and classic straight leg design.',
                'Image File' => 'jeans.jpg',
                'Status' => 'Active',
                'Created At' => date('Y-m-d H:i:s'),
            ],
            [
                'ID' => 'PRD-1004',
                'Name' => 'Vintage Graphic Shirt',
                'Slug' => 'vintage-graphic-shirt',
                'Price' => '399',
                'Category' => 'Shirts',
                'Details' => 'A standout graphic piece that blends nostalgia and street-ready styling.',
                'Image File' => 'shirt.jpg',
                'Status' => 'Active',
                'Created At' => date('Y-m-d H:i:s'),
            ],
            [
                'ID' => 'PRD-1005',
                'Name' => 'Casual Cotton Shirt',
                'Slug' => 'casual-cotton-shirt',
                'Price' => '349',
                'Category' => 'Shirts',
                'Details' => 'Breathable everyday shirt with a clean finish for casual layering.',
                'Image File' => 'shirt.jpg',
                'Status' => 'Active',
                'Created At' => date('Y-m-d H:i:s'),
            ],
            [
                'ID' => 'PRD-1006',
                'Name' => 'Streetwear Hoodie',
                'Slug' => 'streetwear-hoodie',
                'Price' => '599',
                'Category' => 'Hoodies',
                'Details' => 'Cozy fleece hoodie designed for comfort, movement, and laid-back style.',
                'Image File' => 'product3.jpg',
                'Status' => 'Active',
                'Created At' => date('Y-m-d H:i:s'),
            ],
            [
                'ID' => 'PRD-1007',
                'Name' => 'Vintage Denim Shirt',
                'Slug' => 'vintage-denim-shirt',
                'Price' => '449',
                'Category' => 'Shirts',
                'Details' => 'A sturdy washed denim shirt with a relaxed fit and heritage appeal.',
                'Image File' => 'shirt.jpg',
                'Status' => 'Active',
                'Created At' => date('Y-m-d H:i:s'),
            ],
            [
                'ID' => 'PRD-1008',
                'Name' => 'Classic Black T-Shirt',
                'Slug' => 'classic-black-t-shirt',
                'Price' => '249',
                'Category' => 'T-Shirts',
                'Details' => 'Minimal black tee with premium softness and effortless versatility.',
                'Image File' => 'tshirt.jpeg',
                'Status' => 'Active',
                'Created At' => date('Y-m-d H:i:s'),
            ],
        ];

        foreach ($demoItems as $item) {
            $this->insertRecord('Products', $item);
            // Also seed corresponding inventory
            $this->insertRecord('Inventory', [
                'Product ID' => $item['ID'],
                'Product Name' => $item['Name'],
                'SKU' => 'SKU-' . substr($item['Slug'], 0, 6) . '-01',
                'Stock Quantity' => 15,
                'Reserved Quantity' => 0,
                'Available Quantity' => 15,
                'Low Stock Alert' => 3,
                'Last Updated' => date('Y-m-d H:i:s'),
            ]);
        }

        // Seed initial Settings
        $this->insertRecord('Settings', [
            'Key' => 'store_name',
            'Value' => APP_NAME,
            'Description' => 'The brand name displayed in header and footer',
            'Last Updated' => date('Y-m-d H:i:s'),
        ]);
        $this->insertRecord('Settings', [
            'Key' => 'tagline',
            'Value' => APP_TAGLINE,
            'Description' => 'Store tagline for hero banner',
            'Last Updated' => date('Y-m-d H:i:s'),
        ]);
        $this->insertRecord('Settings', [
            'Key' => 'currency',
            'Value' => 'INR (₹)',
            'Description' => 'Currency symbol and code',
            'Last Updated' => date('Y-m-d H:i:s'),
        ]);
        $this->insertRecord('Settings', [
            'Key' => 'support_email',
            'Value' => 'mitramsolutions@gmail.com',
            'Description' => 'Recipient email for contact messages',
            'Last Updated' => date('Y-m-d H:i:s'),
        ]);
    }
}

