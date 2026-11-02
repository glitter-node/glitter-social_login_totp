(function (window) {
    'use strict';

    var copyButtonId = 'glitter-social_login_totp-registration-totp-copy';
    var manualKeyId = 'glitter-social_login_totp-registration-totp-manual-key';
    var copySuccessId = 'glitter-social_login_totp-registration-totp-copy-success';
    var copyFailedId = 'glitter-social_login_totp-registration-totp-copy-failed';

    function copyManualKey(input) {
        if (navigator.clipboard && window.isSecureContext) {
            return navigator.clipboard.writeText(input.value);
        }

        input.focus();
        input.select();
        if (!document.execCommand('copy')) {
            return Promise.reject(new Error('clipboard_unavailable'));
        }

        return Promise.resolve();
    }

    function handleCopy(event) {
        var target = event.target;
        var button = target && target.closest ? target.closest('#' + copyButtonId) : null;
        if (!button) {
            return;
        }

        event.preventDefault();

        var input = document.getElementById(manualKeyId);
        if (!input || typeof input.value !== 'string' || input.value === '') {
            return;
        }

        var originalText = button.textContent;
        copyManualKey(input).then(function () {
            var success = document.getElementById(copySuccessId);
            button.textContent = success ? success.textContent : originalText;
            window.setTimeout(function () {
                button.textContent = originalText;
            }, 1800);
        }).catch(function () {
            var failed = document.getElementById(copyFailedId);
            button.textContent = failed ? failed.textContent : originalText;
        });
    }

    function bindCopyHandler() {
        if (typeof document === 'undefined' || window.__glitterSocialLoginTOTPCopyBound) {
            return;
        }

        document.addEventListener('click', handleCopy);
        window.__glitterSocialLoginTOTPCopyBound = true;
    }

    bindCopyHandler();

    window.GlitterSocialLoginTOTP = Object.freeze({
        identifier: 'glitter-social_login_totp',
        apiPrefix: '/api/plugins/glitter-social_login_totp',
        providers: Object.freeze(['naver', 'kakao', 'google', 'facebook', 'github']),
    });
}(window));
