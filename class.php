<?php
if (!defined('_ZB_PATH')) return;

final class Request {

    public static function get(string $key, string $default = ''): string {
        return isset($_GET[$key]) && is_string($_GET[$key]) ? $_GET[$key] : $default;
    }

    public static function getArr(string $key, array $default = array()): array {
        return isset($_GET[$key]) && is_array($_GET[$key]) ? $_GET[$key] : $default;
    }

    public static function post(string $key, string $default = ''): string {
        return isset($_POST[$key]) && is_string($_POST[$key]) ? $_POST[$key] : $default;
    }

    public static function postArr(string $key, array $default = array()): array {
        return isset($_POST[$key]) && is_array($_POST[$key]) ? $_POST[$key] : $default;
    }

    public static function req(string $key, string $default = ''): string {
        if (isset($_POST[$key]) && is_string($_POST[$key])) return $_POST[$key];
        if (isset($_GET[$key]) && is_string($_GET[$key])) return $_GET[$key];
        return $default;
    }

    public static function reqArr(string $key, array $default = array()): array {
        if (isset($_POST[$key]) && is_array($_POST[$key])) return $_POST[$key];
        if (isset($_GET[$key]) && is_array($_GET[$key])) return $_GET[$key];
        return $default;
    }

    public static function cookie(string $key, string $default = ''): string {
        return isset($_COOKIE[$key]) && is_string($_COOKIE[$key]) ? $_COOKIE[$key] : $default;
    }

    public static function cookieArr(string $key, array $default = array()): array {
        return isset($_COOKIE[$key]) && is_array($_COOKIE[$key]) ? $_COOKIE[$key] : $default;
    }

    public static function header(string $key, string $default = ''): string {
        $k = strtoupper(str_replace('-', '_', $key));
        if (!in_array($k, ['CONTENT_TYPE', 'CONTENT_LENGTH'], true)) $k = 'HTTP_' . $k;

        return isset($_SERVER[$k]) && is_string($_SERVER[$k]) ? $_SERVER[$k] : $default;
    }

    public static function method(string $default = 'GET'): string {
        return isset($_SERVER['REQUEST_METHOD']) && is_string($_SERVER['REQUEST_METHOD']) ? $_SERVER['REQUEST_METHOD'] : $default;
    }

    public static function isHttps(): bool {
        return isset($_SERVER['HTTPS']) && is_string($_SERVER['HTTPS']) && strtolower($_SERVER['HTTPS']) !== 'off';
    }

    public static function uri(string $default = ''): string {
        return isset($_SERVER['REQUEST_URI']) && is_string($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : $default;
    }

    public static function scriptName(string $default = ''): string {
        return isset($_SERVER['SCRIPT_NAME']) && is_string($_SERVER['SCRIPT_NAME']) ? $_SERVER['SCRIPT_NAME'] : $default;
    }

    public static function clientIp(string $default = ''): string {
        return isset($_SERVER['REMOTE_ADDR']) && is_string($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : $default;
    }

    public static function phpSelf(string $default = ''): string {
        trigger_error('Method Request::phpSelf() is deprecated, use Request::scriptName() instead', E_USER_DEPRECATED);

        return isset($_SERVER['PHP_SELF']) && is_string($_SERVER['PHP_SELF']) ? $_SERVER['PHP_SELF'] : $default;
    }

    // 단일 파일 업로드 (input type=file name="key"). 파일이 없으면 null.
    public static function file(string $key): ?UploadedFile {
        if (!isset($_FILES[$key]) || !is_array($_FILES[$key]) || !isset($_FILES[$key]['name'])) return null;
        $f = $_FILES[$key];
        if (is_array($f['name'])) return null; // 다중 필드는 files() 사용
        if ((int) ($f['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) return null;

        return new UploadedFile((string) $f['name'], (string) $f['tmp_name'], (string) ($f['type'] ?? ''), (int) $f['size'], (int) $f['error']);
    }

    // 다중 파일 업로드 (input type=file name="key[]"). 빈 슬롯은 제외한 UploadedFile 배열.
    public static function files(string $key): array {
        if (!isset($_FILES[$key]) || !is_array($_FILES[$key]) || !isset($_FILES[$key]['name'])) return array();
        $f = $_FILES[$key];

        if (!is_array($f['name'])) {
            $one = self::file($key);
            return $one ? array($one) : array();
        }

       return self::collectFiles($f['name'], $f['tmp_name'], $f['type'] ?? array(), $f['size'], $f['error']);
    }

    private static function collectFiles(array $names, array $tmpNames, array $types, array $sizes, array $errors): array {
        $out = array();
        
        foreach ($names as $i => $name) {
            if (is_array($name)) {
                $out = array_merge($out, self::collectFiles($name, $tmpNames[$i], $types[$i] ?? array(), $sizes[$i], $errors[$i]));
                continue;
            }
            if ((int) ($errors[$i] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) continue;
            $out[] = new UploadedFile((string) $name, (string) $tmpNames[$i], (string) ($types[$i] ?? ''), (int) $sizes[$i], (int) $errors[$i]);
        }

        return $out;
    }
}

final class UploadedFile {

    private $name;
    private $tmpName;
    private $type;
    private $size;
    private $error;

    public function __construct(string $name, string $tmpName, string $type, int $size, int $error) {
        $this->name = $name;
        $this->tmpName = $tmpName;
        $this->type = $type;
        $this->size = $size;
        $this->error = $error;
    }

    public function name(): string { return $this->name; }        // 클라이언트 원본 파일명
    public function tmpName(): string { return $this->tmpName; }   // 서버측 임시 경로
    public function type(): string { return $this->type; }        // 클라이언트 제공 MIME
    public function size(): int { return $this->size; }
    public function error(): int { return $this->error; }

    // 정상 업로드 여부 (error 코드 + 임시파일이 실제 업로드분인지)
    public function isValid(): bool {
        return $this->error === UPLOAD_ERR_OK && is_uploaded_file($this->tmpName);
    }

    // 확장자(소문자, 점 없음). 정확 매치용 — 파일명 부분매치(preg_match)와 동작이 다르니 주의.
    public function extension(): string {
        return strtolower(pathinfo($this->name, PATHINFO_EXTENSION));
    }

    // 임시파일을 목적지로 이동 (move_uploaded_file 래핑)
    public function moveTo(string $destination): bool {
        return move_uploaded_file($this->tmpName, $destination);
    }
}

final class Response {

    private static function cookieOptions(int $expires): array {
        return [
            'expires' => $expires,
            'path' => defined('_ZB_BASE') ? _ZB_BASE : '/',
            'httponly' => true,
            'samesite' => 'Lax',
            'secure' => Request::isHttps()
        ];
    }

    public static function cookie(string $key, string $value, int $expires = 0): bool {
        if (headers_sent()) return false;

        $options = self::cookieOptions($expires);

        if (PHP_VERSION_ID >= 70300) {
            return setcookie($key, $value, $options);
        }

        $path = $options['path'] . '; SameSite=Lax';
        return setcookie($key, $value, $options['expires'], $path, '', $options['secure'], $options['httponly']);
    }

    public static function removeCookie(string $key): bool {
        if (headers_sent()) return false;
        unset($_COOKIE[$key]);

        $options = self::cookieOptions(time() - 3600);

        if (PHP_VERSION_ID >= 70300) {
            return setcookie($key, '', $options);
        }

        $path = $options['path'] . '; SameSite=Lax';
        return setcookie($key, '', $options['expires'], $path, '', $options['secure'], $options['httponly']);
    }

    public static function header(string $key, string $value): bool {
        if (headers_sent()) return false;

        header($key . ': ' . $value);
        return true;
    }

    public static function redirect(string $url): void {
        if (headers_sent()) echo '<meta http-equiv="refresh" content="0; url=' . htmlspecialchars($url, ENT_QUOTES | ENT_SUBSTITUTE | ENT_HTML401, 'UTF-8') . '">';
        else header('Location: ' . $url);

        exit();
    }
}

final class Session {

    public static function isActive(): bool {
        return session_status() === PHP_SESSION_ACTIVE;
    }

    public static function start(string $savePath = ''): void {
        if (self::isActive()) return;

        if ($savePath === '' && defined('SESSION_PATH')) session_save_path(SESSION_PATH);
        elseif ($savePath !== '') session_save_path($savePath);

        session_cache_limiter('nocache');
        ini_set('session.use_strict_mode', '1');

        if (PHP_VERSION_ID >= 70300) {
            session_set_cookie_params([
                'lifetime' => 0,
                'path' => defined('_ZB_BASE') ? _ZB_BASE : '/',
                'httponly' => true,
                'samesite' => 'Lax',
                'secure' => Request::isHttps()
            ]);
        } else {
            session_set_cookie_params(
                0,
                (defined('_ZB_BASE') ? _ZB_BASE : '/') . '; SameSite=Lax',
                '',
                Request::isHttps(),
                true
            );
        }
        session_start();
    }

    public static function id(): string {
        if (!self::isActive()) throw new RuntimeException('Session not started');

        return session_id();
    }

    public static function get(string $key, $default = null) {
        if (!self::isActive()) throw new RuntimeException('Session not started');

        return $_SESSION[$key] ?? $default;
    }

    public static function set(string $key, $value): void {
        if (!self::isActive()) throw new RuntimeException('Session not started');

        $_SESSION[$key] = $value;
    }

    public static function has(string $key): bool {
        if (!self::isActive()) throw new RuntimeException('Session not started');

        return isset($_SESSION[$key]);
    }

    public static function remove(string $key): void {
        if (!self::isActive()) throw new RuntimeException('Session not started');

        unset($_SESSION[$key]);
    }

    public static function regenerate(): void {
        if (!self::isActive()) throw new RuntimeException('Session not started');

        session_regenerate_id(true);
    }

    public static function destroy(): void {
        if (!self::isActive()) throw new RuntimeException('Session not started');

        $_SESSION = array();
        session_destroy();
    }

    // CSRF 동기화 토큰 (세션당 1회 생성, 요청당 검증)
    public static function csrfToken(): string {
        if (!self::isActive()) throw new RuntimeException('Session not started');
        if (empty($_SESSION['zb_csrf_token']) || !is_string($_SESSION['zb_csrf_token'])) {
            $_SESSION['zb_csrf_token'] = bin2hex(random_bytes(32));
        }

        return $_SESSION['zb_csrf_token'];
    }

    // 제출된 토큰이 세션 토큰과 일치하는지 비교
    public static function checkCsrf(?string $token): bool {
        if (!self::isActive()) return false;
        $stored = $_SESSION['zb_csrf_token'] ?? '';

        return is_string($stored) && $stored !== '' && is_string($token) && hash_equals($stored, $token);
    }
}

final class DB {

    private $conn;

    public function __construct(string $host, string $user, string $pass, string $name, string $charset = 'utf8mb4') {
        $port = 3306;
        $originalHost = $host;

        if ($host !== '' && $host[0] === '[') {
            $close = strpos($host, ']');
            if ($close === false) throw new InvalidArgumentException('Invalid IPv6 host : ' . $originalHost);
            $rest = substr($host, $close + 1);
            $host = substr($host, 1, $close - 1);
            if ($rest !== '') {
                if ($rest[0] !== ':' || !preg_match('/^\d+$/', substr($rest, 1)))
                    throw new InvalidArgumentException('Invalid host : ' . $originalHost);
                $port = (int) substr($rest, 1);
            }
        } elseif (substr_count($host, ':') === 1) {
            list($host, $p) = explode(':', $host, 2);
            if (!preg_match('/^\d+$/', $p)) throw new InvalidArgumentException('Invalid port : ' . $p);
            $port = (int) $p;
        }

        if ($port < 1 || $port > 65535) throw new InvalidArgumentException('Port must be an integer between 1 and 65535');

        mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
        $this->conn = new mysqli($host, $user, $pass, $name, $port);
        $this->conn->set_charset($charset);
    }

    private function types(array $params): string {
        $t = '';
        foreach ($params as $p) {
            if (is_int($p)) $t .= 'i';
            elseif (is_float($p)) $t .= 'd';
            else $t .= 's';
        }

        return $t;
    }

    public function query(string $sql, array $params = array()): mysqli_stmt {
        $stmt = $this->conn->prepare($sql);

        if ($params) $stmt->bind_param($this->types($params), ...array_values($params));

        $stmt->execute();
        return $stmt;
    }

    // NULL 컬럼을 빈 문자열로 변환
    private function nullToEmpty(array $row): array {
        foreach ($row as $k => $v) if ($v === null) $row[$k] = '';
        return $row;
    }

    public function row(string $sql, array $params = array(), bool $nullToEmpty = true): array {
        $r = $this->query($sql, $params)->get_result()->fetch_assoc();

        if (!is_array($r)) return array();

        return $nullToEmpty ? $this->nullToEmpty($r) : $r;
    }

    public function all(string $sql, array $params = array(), bool $nullToEmpty = true): array {
        $rows = $this->query($sql, $params)->get_result()->fetch_all(MYSQLI_ASSOC);

        return $nullToEmpty ? array_map([$this, 'nullToEmpty'], $rows) : $rows;
    }

    public function value(string $sql, array $params = array(), string $default = ''): string {
        $r = $this->query($sql, $params)->get_result()->fetch_row();

        return isset($r[0]) && (is_string($r[0]) || is_int($r[0]) || is_float($r[0])) ? (string) $r[0] : $default;
    }

    public function exec(string $sql, array $params = array()): int {
        return $this->query($sql, $params)->affected_rows;
    }

    public function insertId(): int {
        return (int) $this->conn->insert_id;
    }

    public function escape(string $value): string {
        return $this->conn->real_escape_string($value);
    }

    /**
     * LIKE 절의 특수문자 (% 및 _) 를 이스케이프 하는 함수
     * DB::escape() 이전에 사용할 것
     * 
     * 사용 예시(바인딩 권장): $connect->all("... where name like ?", ['%'.$connect->escapeLike($keyword).'%'])
     */
    public function escapeLike(string $value): string {
        return addcslashes($value, '_%\\');
    }

    public function escapeIdentifier(string $value): string {
        return '`' . str_replace('`', '``', $value) . '`';
    }

    public function raw(): mysqli {
        return $this->conn;
    }

    public function tableExists(string $table): bool {
        return $this->value('SELECT 1 FROM information_schema.tables WHERE table_schema = DATABASE() AND table_name = ?', [$table]) !== '';
    }
}
