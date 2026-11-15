<?php
/**
 * ANSH THRIFTS — Pure PHP SMTP Mailer Client
 * Supports SSL/TLS authentication, RFC 2822 compliance, UTF-8 encoded text/html,
 * Reply-To headers, and credential-safe error logging.
 */

declare(strict_types=1);

require_once __DIR__ . '/config.php';

class SmtpMailer {
    private string $host;
    private int $port;
    private string $username;
    private string $password;
    private int $timeout;

    public function __construct(
        ?string $host = null,
        ?int $port = null,
        ?string $username = null,
        ?string $password = null,
        int $timeout = 15
    ) {
        $this->host = $host ?? MAIL_SERVER;
        $this->port = $port ?? MAIL_PORT;
        $this->username = $username ?? MAIL_USERNAME;
        $this->password = $password ?? MAIL_PASSWORD;
        $this->timeout = $timeout;
    }

    /**
     * Send email over secure SMTP.
     *
     * @param string $to Recipient email address
     * @param string $subject Subject line
     * @param string $htmlBody HTML content
     * @param string|null $replyTo Optional reply-to address
     * @param string|null $replyToName Optional reply-to name
     * @return bool True on success, throws Exception on failure
     * @throws RuntimeException
     */
    public function send(
        string $to,
        string $subject,
        string $htmlBody,
        ?string $replyTo = null,
        ?string $replyToName = null
    ): bool {
        if (empty($this->username) || empty($this->password)) {
            throw new RuntimeException("SMTP credentials not configured.");
        }

        $context = stream_context_create([
            'ssl' => [
                'verify_peer' => true,
                'verify_peer_name' => true,
                'allow_self_signed' => false,
            ]
        ]);

        $protocol = ($this->port === 465) ? 'ssl://' : '';
        $socket = @stream_socket_client(
            $protocol . $this->host . ':' . $this->port,
            $errno,
            $errstr,
            $this->timeout,
            STREAM_CLIENT_CONNECT,
            $context
        );

        if (!$socket) {
            error_log("ANSH THRIFTS Mailer: Connection to {$this->host}:{$this->port} failed (code $errno).");
            throw new RuntimeException("Failed to connect to SMTP server.");
        }

        stream_set_timeout($socket, $this->timeout);

        try {
            $this->readResponse($socket, '220');

            // Send EHLO
            $this->sendCommand($socket, "EHLO " . ($_SERVER['SERVER_NAME'] ?? 'anshthrifts.in'), '250');

            // Handle STARTTLS for port 587 if needed
            if ($this->port !== 465) {
                $this->sendCommand($socket, "STARTTLS", '220');
                if (!stream_socket_enable_crypto($socket, true, STREAM_CRYPTO_METHOD_TLS_CLIENT)) {
                    throw new RuntimeException("TLS negotiation failed.");
                }
                $this->sendCommand($socket, "EHLO " . ($_SERVER['SERVER_NAME'] ?? 'anshthrifts.in'), '250');
            }

            // Authenticate with AUTH LOGIN
            $this->sendCommand($socket, "AUTH LOGIN", '334');
            $this->sendCommand($socket, base64_encode($this->username), '334');
            $this->sendCommand($socket, base64_encode($this->password), '235');

            // Envelope From
            $this->sendCommand($socket, "MAIL FROM:<" . $this->username . ">", '250');

            // Envelope To
            $this->sendCommand($socket, "RCPT TO:<" . trim($to) . ">", '250');

            // DATA
            $this->sendCommand($socket, "DATA", '354');

            // Build Headers
            $fromName = MAIL_FROM_NAME;
            $encodedFromName = '=?UTF-8?B?' . base64_encode($fromName) . '?=';
            $encodedSubject = '=?UTF-8?B?' . base64_encode($subject) . '?=';

            $headers = [];
            $headers[] = "Date: " . date('r');
            $headers[] = "From: {$encodedFromName} <{$this->username}>";
            $headers[] = "To: <" . trim($to) . ">";
            if (!empty($replyTo)) {
                $replyNamePart = !empty($replyToName) ? '=?UTF-8?B?' . base64_encode($replyToName) . '?= ' : '';
                $headers[] = "Reply-To: {$replyNamePart}<" . trim($replyTo) . ">";
            }
            $headers[] = "Subject: {$encodedSubject}";
            $headers[] = "MIME-Version: 1.0";
            $headers[] = "Content-Type: text/html; charset=UTF-8";
            $headers[] = "Content-Transfer-Encoding: base64";
            $headers[] = "X-Mailer: ANSH THRIFTS Mailer";

            $payload = implode("\r\n", $headers) . "\r\n\r\n";
            $payload .= chunk_split(base64_encode($htmlBody)) . "\r\n.";

            $this->sendCommand($socket, $payload, '250');

            // Clean Quit
            $this->sendCommand($socket, "QUIT", '221');
            @fclose($socket);

            return true;
        } catch (\Throwable $e) {
            @fclose($socket);
            // Log generic diagnostic without private passwords
            error_log("ANSH THRIFTS Mailer Error sending to recipient: " . $e->getMessage());
            throw $e;
        }
    }

    private function sendCommand($socket, string $command, string $expectedCode): string {
        fputs($socket, $command . "\r\n");
        return $this->readResponse($socket, $expectedCode);
    }

    private function readResponse($socket, string $expectedCode): string {
        $response = "";
        while (!feof($socket)) {
            $line = fgets($socket, 512);
            if ($line === false) {
                break;
            }
            $response .= $line;
            // Complete multiline response ends with code followed by space
            if (strlen($line) >= 4 && substr($line, 0, 3) === $expectedCode && $line[3] === ' ') {
                break;
            }
            // Error codes returned
            if (strlen($line) >= 4 && in_array($line[0], ['4', '5'], true)) {
                throw new RuntimeException("SMTP Command Error [{$line[0]}xx]: " . trim($line));
            }
        }

        if (!str_starts_with(trim($response), $expectedCode)) {
            throw new RuntimeException("Unexpected SMTP response (Expected {$expectedCode}): " . trim($response));
        }

        return $response;
    }
}

