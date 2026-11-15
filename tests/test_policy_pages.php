<?php
/**
 * ANSH THRIFTS — Automated Policy & Legal Suite Verification Test
 */

declare(strict_types=1);

$baseUrl = 'http://localhost:8080';
$allPassed = true;
$results = [];

function recordTest(string $title, bool $passed, string $details = ''): void {
    global $results, $allPassed;
    if (!$passed) {
        $allPassed = false;
    }
    $results[] = [
        'title' => $title,
        'passed' => $passed,
        'details' => $details,
    ];
    $statusText = $passed ? "\033[32mPASS\033[0m" : "\033[31mFAIL\033[0m";
    echo sprintf("[%s] %s %s\n", $statusText, $title, $details ? "— $details" : "");
}

echo "====================================================\n";
echo "   ANSH THRIFTS LEGAL & POLICIES TEST SUITE\n";
echo "====================================================\n\n";

// 1. Test PHP files render without errors and contain required sections
$policyPages = [
    'privacy-policy.php' => [
        'title' => 'Privacy Policy',
        'keywords' => ['Commitment to Your Privacy', 'Razorpay', 'Google', 'Grievance Officer', 'OWNER INPUT REQUIRED'],
    ],
    'refund-policy.php' => [
        'title' => 'Return, Refund & Exchange Policy',
        'keywords' => ['The Nature of Thrift & Vintage Goods', 'Final Sale', '1-of-1', 'Unboxing Video', 'OWNER INPUT REQUIRED'],
    ],
    'terms.php' => [
        'title' => 'Terms & Conditions',
        'keywords' => ['Information Technology Act', '1-of-1', 'Razorpay', 'Limitation of Liability', 'OWNER INPUT REQUIRED'],
    ],
    'shipping-policy.php' => [
        'title' => 'Shipping & Delivery Policy',
        'keywords' => ['Domestic Shipping Across India', 'Dispatch Timelines', 'Metro Cities', 'AWB', 'OWNER INPUT REQUIRED'],
    ],
    'cancellation-policy.php' => [
        'title' => 'Cancellation Policy',
        'keywords' => ['Cancellation Window', 'Prior to Dispatch', 'Cash on Delivery', '1-of-1', 'OWNER INPUT REQUIRED'],
    ],
    'payment-policy.php' => [
        'title' => 'Payment Policy',
        'keywords' => ['Razorpay', 'PCI-DSS', 'UPI', 'Cash on Delivery', 'OWNER INPUT REQUIRED'],
    ],
    'grievance.php' => [
        'title' => 'Grievance Redressal Policy',
        'keywords' => ['Consumer Protection', 'Grievance Officer Details', '48 hours', '30 days', 'National Consumer Helpline'],
    ],
    'contact-info.php' => [
        'title' => 'Contact Information',
        'keywords' => ['Statutory Legal Disclosures', 'Registered Office', 'GSTIN', 'OWNER INPUT REQUIRED'],
    ],
];

foreach ($policyPages as $file => $spec) {
    $filePath = __DIR__ . '/../' . $file;
    if (!file_exists($filePath)) {
        recordTest("File existence: $file", false, "File missing from disk");
        continue;
    }
    recordTest("File existence: $file", true, "Present on disk");

    // Execute script in isolation and buffer output
    ob_start();
    try {
        $_SERVER['HTTP_HOST'] = 'anshthrifts.in';
        $_SERVER['REQUEST_URI'] = '/' . $file;
        require $filePath;
        $html = ob_get_clean();
        
        $rendered = strlen($html) > 500;
        recordTest("Execution & Render: $file", $rendered, "Rendered " . strlen($html) . " bytes");

        // Verify required keywords
        $allKeywordsFound = true;
        $missingKeywords = [];
        foreach ($spec['keywords'] as $kw) {
            if (stripos($html, $kw) === false) {
                $allKeywordsFound = false;
                $missingKeywords[] = $kw;
            }
        }
        recordTest("Key Sections & Legal Terms in $file", $allKeywordsFound, 
            $allKeywordsFound ? "All required sections verified" : "Missing: " . implode(', ', $missingKeywords));

    } catch (\Throwable $e) {
        ob_end_clean();
        recordTest("Execution & Render: $file", false, "Exception: " . $e->getMessage());
    }
}

// 2. Test Footer Links across pages
echo "\n--- Verifying Global Footer & Links ---\n";
$footerPath = __DIR__ . '/../includes/footer.php';
$footerContent = file_get_contents($footerPath);

$expectedLinks = [
    '/refund-policy.php',
    '/shipping-policy.php',
    '/cancellation-policy.php',
    '/payment-policy.php',
    '/privacy-policy.php',
    '/terms.php',
    '/grievance.php',
    '/contact-info.php',
];

$allLinksPresent = true;
foreach ($expectedLinks as $link) {
    if (strpos($footerContent, $link) === false) {
        $allLinksPresent = false;
        recordTest("Footer link: $link", false, "Link missing from includes/footer.php");
    }
}
if ($allLinksPresent) {
    recordTest("Footer links completeness", true, "All 8 policy routes present in global footer");
}

// 3. Test Checkout Cart Acknowledgement
echo "\n--- Verifying Cart Checkout Acknowledgement ---\n";
$cartPath = __DIR__ . '/../cart.php';
$cartContent = file_get_contents($cartPath);

$hasCheckbox = strpos($cartContent, 'policyAgreeCheck') !== false;
recordTest("Cart Policy Consent Checkbox", $hasCheckbox, "Required checkbox with id 'policyAgreeCheck' present");

$hasValidation = strpos($cartContent, 'policyCheck && !policyCheck.checked') !== false;
recordTest("Cart Checkout JS Validation", $hasValidation, "JS validation prevents checkout if unchecked");

$hasTermsLink = strpos($cartContent, '/terms.php') !== false &&
                strpos($cartContent, '/privacy-policy.php') !== false &&
                strpos($cartContent, '/refund-policy.php') !== false;
recordTest("Cart Policy Links", $hasTermsLink, "Terms, Privacy, and Refund links visible at checkout");

// 4. Test Sitemap XML
echo "\n--- Verifying Sitemap XML ---\n";
$sitemapPath = __DIR__ . '/../sitemap.xml';
$hasSitemap = file_exists($sitemapPath);
recordTest("sitemap.xml exists", $hasSitemap);
if ($hasSitemap) {
    $sitemapContent = file_get_contents($sitemapPath);
    $sitemapValid = true;
    foreach ($expectedLinks as $link) {
        $cleanLink = ltrim($link, '/');
        if (strpos($sitemapContent, $cleanLink) === false) {
            $sitemapValid = false;
        }
    }
    recordTest("sitemap.xml contains all policy URLs", $sitemapValid, "All 8 policy URLs indexed");
}

echo "\n====================================================\n";
if ($allPassed) {
    echo "\033[32mALL POLICY TESTS PASSED SUCCESSFULLY! (100% PASS)\033[0m\n";
} else {
    echo "\033[31mSOME TESTS FAILED. PLEASE REVIEW LOG ABOVE.\033[0m\n";
    exit(1);
}
echo "====================================================\n";

