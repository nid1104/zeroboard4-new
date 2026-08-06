<?php
if (!defined('_ZB_PATH')) exit();

// URL을 담는 속성 (프로토콜 검사 대상)
const ZB_URL_ATTR = array('href', 'src', 'lowsrc', 'dynsrc', 'background', 'action', 'formaction', 'poster', 'cite', 'longdesc');

// 허용 URL 스킴 (그 외 스킴은 거부, 스킴이 없으면 상대경로로 간주해 허용)
const ZB_URL_SCHEMES = array('http', 'https', 'ftp', 'mailto');


/**
 * URL 스킴이 안전한지 검사. 공백 및 제어문자 제거 후
 * 스킴이 있으면 화이트리스트에 있어야 하고, 스킴이 없으면(상대/앵커/쿼리) 허용.
 */
function zb_url_scheme_ok($url): bool {
    $u = preg_replace('/[\x00-\x20]+/', '', (string) $url);
    if ($u === '') return true;
    if (preg_match('#^([a-z][a-z0-9+.\-]*):#i', $u, $m)) {
        return in_array(strtolower($m[1]), ZB_URL_SCHEMES, true);
    }
    return true;
}


/**
 * HTML sanitize. 허용 태그는 인자로 받는다(관리자 설정 구동)
 * 실패 또는 미지원시 안전측으로 전체 escape 반환
 *
 * @param string $html          정화할 HTML
 * @param array  $allowed_tags  허용 태그명 목록 (예: ['a','b','img','font']).
 */
function zb_sanitize_html(string $html, array $allowed_tags): string {
    if ($html === '' || strpos($html, '<') === false) return $html;

    if (!class_exists('DOMDocument')) {
        return htmlspecialchars($html, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    }

    // 허용 태그를 소문자 셋으로 정규화
    $allow = array();
    foreach ($allowed_tags as $t) {
        $t = strtolower(trim((string) $t));
        if ($t !== '') $allow[$t] = true;
    }

    $safe = preg_replace('#<(/?)zbwrap#i', '&lt;$1zbwrap', $html);

    $dom = new DOMDocument();
    $prev = libxml_use_internal_errors(true);
    $wrapped = '<?xml encoding="UTF-8"><zbwrap>' . $safe . '</zbwrap>';
    $ok = $dom->loadHTML(
        $wrapped,
        LIBXML_NOERROR | LIBXML_NOWARNING | LIBXML_NONET | LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD
    );
    libxml_clear_errors();
    libxml_use_internal_errors($prev);

    if (!$ok) return htmlspecialchars($html, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');

    $root = $dom->getElementsByTagName('zbwrap')->item(0);
    if ($root === null) return htmlspecialchars($html, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');

    zb_sanitize_node($root, $allow);

    $out = '';
    foreach (iterator_to_array($root->childNodes) as $child) {
        $out .= $dom->saveHTML($child);
    }
    return $out;
}


/**
 * 노드의 자식들을 재귀 정화.
 *  - 허용 태그   : 속성 정화(on*, 위험 URL, 위험 style만 제거) 후 재귀
 *  - 미허용 태그 : 자식 정화 후 태그를 텍스트로 escape(내용 유지)
 *  - 주석/CDATA/PI : 제거
 */
function zb_sanitize_node(DOMNode $node, array $allow): void {
    foreach (iterator_to_array($node->childNodes) as $child) {
        switch ($child->nodeType) {
            case XML_ELEMENT_NODE:
                $tag = strtolower($child->nodeName);
                if (isset($allow[$tag])) {
                    zb_sanitize_attributes($child);   // 속성 레벨 JS 차단만
                    zb_sanitize_node($child, $allow);
                } else {
                    zb_sanitize_node($child, $allow);  // 자식 먼저 정화
                    zb_escape_tag_to_text($child);     // 태그를 텍스트로
                }
                break;

            case XML_TEXT_NODE:
                break; // 텍스트 유지 (직렬화 시 <,>,& 재인코딩)

            case XML_CDATA_SECTION_NODE:
                // script/style 등 raw-text 내용이 CDATA로 파싱될 수 있음 -> 텍스트로 보존
                $child->parentNode->replaceChild($child->ownerDocument->createTextNode($child->data), $child);
                break;

            default: // 주석, PI 등
                $child->parentNode->removeChild($child);
        }
    }
}


/**
 * 허용 태그의 속성에서 JS 실행 벡터만 제거, 나머지 속성(id/class/width/align 등)은 유지.
 */
function zb_sanitize_attributes(DOMElement $el): void {
    $names = array();
    foreach ($el->attributes as $a) $names[] = $a->nodeName;

    foreach ($names as $name) {
        $lname = strtolower($name);
        $remove = false;

        if (strncmp($lname, 'on', 2) === 0) $remove = true;
        elseif (in_array($lname, ZB_URL_ATTR, true)
                && !zb_url_scheme_ok($el->getAttribute($name))) $remove = true;
        elseif ($lname === 'style') $remove = true;

        if ($remove) $el->removeAttribute($name);
    }
}


/**
 * 미허용 요소를 "텍스트로 escape" — 여는/닫는 태그를 텍스트 노드로 바꾸고 자식은 유지
 * saveHTML이 텍스트의 <,>,&를 재인코딩하므로 결과는 &lt;tag&gt; 형태로 보임
 */
function zb_escape_tag_to_text(DOMElement $el): void {
    $parent = $el->parentNode;
    $doc = $el->ownerDocument;

    // 여는 태그 텍스트 재구성 (속성 포함 — 원본은 < 만 escape하므로 속성도 텍스트로 남김)
    $open = '<' . $el->nodeName;
    foreach ($el->attributes as $a) {
        $open .= ' ' . $a->nodeName . '="' . $a->nodeValue . '"';
    }
    $open .= '>';

    $parent->insertBefore($doc->createTextNode($open), $el);
    while ($el->firstChild) {
        $parent->insertBefore($el->firstChild, $el); // 자식 승격(유지)
    }
    $parent->insertBefore($doc->createTextNode('</' . $el->nodeName . '>'), $el);
    $parent->removeChild($el);
}
