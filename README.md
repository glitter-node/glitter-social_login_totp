# Glitter Social Login TOTP

글리터 소셜 로그인 TOTP는 네이버·카카오·구글·페이스북·GitHub 소셜 로그인에 TOTP 필수 추가 인증을 적용하는 독립 보안 인증 플러그인입니다.

Glitter Social Login TOTP is an independent social authentication plugin enforcing mandatory TOTP verification for Naver, Kakao, Google, Facebook, and GitHub.

## Product identity

- Plugin ID: `glitter-social_login_totp`
- Version: `1.0.8`
- Composer package: `glitter/social-login-totp`
- PHP namespace: `Glitter\\SocialLoginTOTP`
- Service provider: `SocialLoginTOTPServiceProvider`
- JavaScript global: `GlitterSocialLoginTOTP`

This product is a separate fork. It does not read, migrate, or modify the original social-login plugin’s settings, provider identities, sessions, cache keys, or database tables.

## Authentication policy

New users follow `OAuth → provider identity → registration pending → TOTP enrollment → TOTP confirmation → member creation → login`. Existing users follow `OAuth → provider identity → TOTP challenge → login`.

The provider identity key is `(provider, provider_user_id)`. An email match alone never links accounts. OAuth success never creates a final login session before the plugin’s TOTP policy is satisfied. The plugin does not consult Gnuboard7 `security.two_factor_auth`; its mandatory TOTP policy is independent of that core setting.

## Providers and callback URLs

The API prefix is `/api/plugins/glitter-social_login_totp`.

- Naver: `/api/plugins/glitter-social_login_totp/naver/callback`
- Kakao: `/api/plugins/glitter-social_login_totp/kakao/callback`
- Google: `/api/plugins/glitter-social_login_totp/google/callback`
- Facebook: `/api/plugins/glitter-social_login_totp/facebook/callback`
- GitHub: `/api/plugins/glitter-social_login_totp/github/callback`

Register the exact HTTPS callback URL for each enabled provider in its developer console. This plugin does not alter provider-console settings and does not copy credentials from another plugin.

## Storage and settings isolation

- Database tables use the `glitter_social_login_totp_` prefix.
- Settings namespace: `glitter-social_login_totp`.
- Settings files: `storage/app/plugins/glitter-social_login_totp/`.
- Environment prefix: `GLITTER_SOCIAL_LOGIN_TOTP_`.
- Cache prefix: `glitter_social_login_totp:`.
- Session prefix: `glitter_social_login_totp.`.
- Event prefix: `glitter.social_login_totp.`.
- Log channel identifier: `glitter_social_login_totp`.

Secrets are supplied through the plugin’s own settings lifecycle. Migrations in this directory are definitions only; this task does not execute them.

## Installation and operation

Review the Gnuboard7 plugin schema and provider callback settings before installation. The new-installation command must be checked against the installed Gnuboard7 version's official CLI contract; this README does not invent or prescribe an unverified installation syntax.

For an existing installation, the previously verified bundled update procedure is:

```bash
/usr/local/bin/php83 artisan plugin:update glitter-social_login_totp --source=bundled --vendor-mode=bundled --layout-strategy=keep --force
```

This is an update command for an existing installation, not a new-installation command. Run it from the appropriate Gnuboard7 application root only during an approved maintenance window. It may modify the installed plugin and application runtime state, so back up the installed plugin and relevant database state first. The command is an intended deployment procedure, not proof that v1.0.8 has been deployed successfully.

Do not disable the original plugin automatically. Before activation, confirm that only one product owns the intended login buttons and callback URLs. Apply migrations through the normal plugin lifecycle only after separate production approval and verification of the installed Gnuboard7 command contract.

## Safe verification

```bash
bash plugins/_bundled/glitter-social_login_totp/docs/verify-production-readonly.sh
```

The verifier checks metadata, PHP syntax, namespace and identifier isolation, table prefixes, provider coverage, and core-TOTP independence. It does not log in, create users, change a database, clear caches, or run a plugin lifecycle command.

## Security invariants

TOTP secrets are encrypted at rest, codes are validated with the configured validity window, replay counters and attempt limits remain enforced, and recovery/re-registration stays behind the existing identity and approval policies. Diagnostic logs contain only provider, action, stage, reason, and exception class. Tokens, authorization codes, secrets, pending identifiers, session identifiers, user identifiers, email addresses, and OTP codes are not logged.

## Existing product

The original social-login product is not modified by this fork. Existing users and provider identities are not imported automatically. A deliberate, separately reviewed account-linking process is required if the operator later wants users to use this independent product.
