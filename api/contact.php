<?php
/**
 * POST /api/contact.php — contact form on the site.
 * Fields: name, phone, company, service, message, source; "website" is a honeypot.
 * Responds with JSON {ok: true} or {ok: false, error, fields?}.
 */

declare(strict_types=1);

require dirname(__DIR__) . '/app/bootstrap.php';

const CONTACT_MAX_PER_IP = 5;      // submissions per IP …
const CONTACT_WINDOW = 600;        // … within 10 minutes

if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
    header('Allow: POST');
    json_response(['ok' => false, 'error' => 'method'], 405);
}

// Browsers send Origin on cross-site POSTs; accept only our own host.
$origin = $_SERVER['HTTP_ORIGIN'] ?? '';
if ($origin !== '' && parse_url($origin, PHP_URL_HOST) !== parse_url('http://' . ($_SERVER['HTTP_HOST'] ?? ''), PHP_URL_HOST)) {
    json_response(['ok' => false, 'error' => 'origin'], 403);
}

// Bots fill every field; people never see this one. Pretend success.
if (post_str('website', 200) !== '') {
    json_response(['ok' => true]);
}

$data = [
    'name' => post_str('name', 120),
    'phone' => post_str('phone', 40),
    'company' => post_str('company', 160),
    'service' => post_str('service', 160),
    'message' => post_str('message', 3000),
    'source' => preg_replace('/[^a-z0-9_-]/i', '', post_str('source', 20)),
];

$invalid = [];
if (mb_strlen($data['name']) < 2) {
    $invalid[] = 'name';
}
if (strlen(preg_replace('/\D+/', '', $data['phone'])) < 7) {
    $invalid[] = 'phone';
}
if ($invalid) {
    json_response(['ok' => false, 'error' => 'validation', 'fields' => $invalid], 422);
}

$ip = client_ip();
$db = db();
$recent = $db->prepare('SELECT COUNT(*) FROM requests WHERE ip = ? AND created_at > ?');
$recent->execute([$ip, date('Y-m-d H:i:s', time() - CONTACT_WINDOW)]);
if ((int)$recent->fetchColumn() >= CONTACT_MAX_PER_IP) {
    json_response(['ok' => false, 'error' => 'rate_limit'], 429);
}

$db->prepare('INSERT INTO requests (name, phone, company, service, message, source, ip, user_agent, status, created_at, updated_at)
    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)')
    ->execute([$data['name'], $data['phone'], $data['company'], $data['service'], $data['message'], $data['source'],
        $ip, mb_substr($_SERVER['HTTP_USER_AGENT'] ?? '', 0, 300), 'new', now(), now()]);
$id = (int)$db->lastInsertId();

notify_new_request($id, $data);

json_response(['ok' => true]);


/** Plain-text e-mail to the address from Settings → Notifications (if set). */
function notify_new_request(int $id, array $data): void
{
    $to = setting('notify_email');
    if (!filter_var($to, FILTER_VALIDATE_EMAIL)) {
        return;
    }
    $host = preg_replace('/[^a-z0-9.-]/i', '', $_SERVER['HTTP_HOST'] ?? 'ccs.com.az');
    $host = preg_replace('/^www\./', '', $host);
    $lines = [
        'Yeni sorğu #' . $id,
        '',
        'Ad: ' . $data['name'],
        'Telefon: ' . $data['phone'],
        'Restoran / şirkət: ' . ($data['company'] ?: '—'),
        'Xidmət: ' . ($data['service'] ?: '—'),
        'Mesaj: ' . ($data['message'] ?: '—'),
        '',
        'Admin panel: https://' . $host . ADMIN_URL . '/requests.php?id=' . $id,
    ];
    $subject = '=?UTF-8?B?' . base64_encode('Yeni sorğu: ' . str_replace(["\r", "\n"], ' ', $data['name'])) . '?=';
    $headers = implode("\r\n", [
        'From: Cost Control Services <noreply@' . $host . '>',
        'Content-Type: text/plain; charset=UTF-8',
        'Content-Transfer-Encoding: 8bit',
    ]);
    @mail($to, $subject, implode("\n", $lines), $headers);
}
