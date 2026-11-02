#!/usr/bin/env bash

set -u

PHP_BIN="${PHP_BIN:-/usr/local/bin/php83}"
SCRIPT_DIR="$(CDPATH= cd -- "$(dirname -- "$0")" && pwd -P)"
PLUGIN_ROOT="$(CDPATH= cd -- "${SCRIPT_DIR}/.." && pwd -P)"
FAILURES=0

pass() { printf 'PASS %s\n' "$1"; }
fail() { FAILURES=$((FAILURES + 1)); printf 'FAIL %s\n' "$1"; }

require_file() {
    if [ -f "$1" ]; then pass "$2"; else fail "$2"; fi
}

if [ ! -x "$PHP_BIN" ]; then
    fail "PHP CLI is not executable: ${PHP_BIN}"
fi

require_file "${PLUGIN_ROOT}/plugin.json" 'plugin manifest exists'
require_file "${PLUGIN_ROOT}/composer.json" 'composer manifest exists'
require_file "${PLUGIN_ROOT}/plugin.php" 'plugin entrypoint exists'
require_file "${PLUGIN_ROOT}/src/Providers/SocialLoginTOTPServiceProvider.php" 'service provider exists'
require_file "${PLUGIN_ROOT}/resources/js/GlitterSocialLoginTOTP.js" 'JavaScript global source exists'

if [ -x "$PHP_BIN" ]; then
    if "$PHP_BIN" -r '
        $data = json_decode(file_get_contents($argv[1]), true);
        exit(is_array($data) && ($data["identifier"] ?? null) === "glitter-social_login_totp" && ($data["version"] ?? null) === "1.0.8" ? 0 : 1);
    ' "${PLUGIN_ROOT}/plugin.json"; then pass 'manifest identity and version'; else fail 'manifest identity and version'; fi

    if "$PHP_BIN" -r '
        $data = json_decode(file_get_contents($argv[1]), true);
        $autoload = $data["autoload"]["psr-4"]["Glitter\\SocialLoginTOTP\\"] ?? null;
        exit($data["name"] === "glitter/social-login-totp" && $autoload === "src/Glitter/SocialLoginTOTP/" ? 0 : 1);
    ' "${PLUGIN_ROOT}/composer.json"; then pass 'composer package and PSR-4 mapping'; else fail 'composer package and PSR-4 mapping'; fi
fi

if rg -n -P 'glitter-social_login/|glitter_social_login/|Plugins\\\\Glitter\\\\SocialLogin(?!TOTP)|security\.two_factor_auth' \
    "${PLUGIN_ROOT}/src" "${PLUGIN_ROOT}/plugin.php" "${PLUGIN_ROOT}/resources" "${PLUGIN_ROOT}/database" --glob '*.php' --glob '*.json' --glob '*.js' --glob '*.sh' >/dev/null 2>&1; then
    fail 'runtime source contains an original identifier or core TOTP setting'
else
    pass 'runtime source is independent of the original identifier and core TOTP setting'
fi

if rg -n -P 'glitter-social_login(?!_totp)|glitter_social_login(?!_totp)' \
    "$PLUGIN_ROOT/src" "$PLUGIN_ROOT/resources" "$PLUGIN_ROOT/database" --glob '*.php' --glob '*.json' --glob '*.js' --glob '*.sh' >/dev/null 2>&1; then
    fail 'runtime source contains an original plugin identifier outside conflict protection'
else
    pass 'original plugin identifier is limited to conflict protection'
fi

if rg -n "['\"]glitter_social_login_totps['\"]|['\"]glitter_social_login_(accounts|exchanges|link_nonces|user_flags)['\"]" "${PLUGIN_ROOT}/database" "${PLUGIN_ROOT}/src/Glitter/SocialLoginTOTP" --glob '*.php' >/dev/null 2>&1; then
    fail 'database or model references use a legacy table name'
else
    pass 'database and model references use the dedicated prefix'
fi

for provider in naver kakao google facebook github; do
    if grep -R -Fq -- "${provider}" "${PLUGIN_ROOT}/src"; then pass "provider source: ${provider}"; else fail "provider source: ${provider}"; fi
done

while IFS= read -r file; do
    if [ -x "$PHP_BIN" ] && "$PHP_BIN" -l "$file" >/dev/null 2>&1; then pass "PHP syntax: ${file#"${PLUGIN_ROOT}/"}"; else fail "PHP syntax: ${file#"${PLUGIN_ROOT}/"}"; fi
done < <(find "${PLUGIN_ROOT}/src" -type f -name '*.php' -print | sort)

if [ "$FAILURES" -eq 0 ]; then
    printf 'SUMMARY PASS\n'
    exit 0
fi

printf 'SUMMARY FAILURES=%d\n' "$FAILURES"
exit 1
