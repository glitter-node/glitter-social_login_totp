# Independence contract

Runtime classes use `Glitter\\SocialLoginTOTP`, routes use the `glitter-social_login_totp` URL prefix, and the browser namespace is `GlitterSocialLoginTOTP`.

Persistent plugin data uses `glitter_social_login_totp_`. Settings, environment variables, cache keys, sessions, events, translations, and log channel identifiers use the product-specific values documented in the README. No migration in this product renames or imports an existing social-login table, and no existing identity is automatically transferred.

The original product is not disabled, updated, or modified by this source package. Activation and hook registration fail closed with a conflict error when `glitter-social_login` is already active; the original product is never disabled automatically.
