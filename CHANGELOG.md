# Changelog

## 1.0.8

- Made social-login layout cache invalidation synchronous after settings are saved, so the configured provider order is visible immediately on login, registration, and account-linking screens.

## 1.0.7

- Removed QR renderer exception messages from logs; only the exception class and a fixed error code are recorded.
- Declared the plugin JavaScript through the official Gnuboard7 asset contract.
- Made manual-key copy handling idempotent and prevented the button's default form action.
- Synchronized TOTP transition defaults while keeping all transition settings backend-only.
- Applied plugin-local Socialite/Guzzle connect and request timeouts.

- Added the nullable local QR enrollment response boundary for new social registrations.
- Added QR enrollment UI bindings and manual-key copy support.
- Added local QR enrollment generation using Bacon QR Code v3.1.1, with dasprid/enum 1.0.7 included in the production vendor bundle.
- Isolated PHP SVG QR generation passed; manual TOTP secret-key enrollment remains available as a fallback.
- Browser rendering, QR scanning, and production integration remain unverified and require postdeployment checks.

## 1.0.5

- Aligned the automatically discovered service provider namespace with the core plugin directory-to-namespace contract.

## 1.0.4

- Removed email-only automatic linking to existing accounts; existing members must use the authenticated explicit linking flow.
- Preserved verified provider email only as a new-registration field constraint.
- Added the missing Korean and English profile operation-failure translation.
- Preserved automatic HTTPS detection for the OAuth browser-binding cookie when the secure-cookie setting is unset.
- Registered the dedicated log channel at its nested runtime configuration key.
- Synchronized the Composer lock content hash and expanded security regression contracts.

## 1.0.3

- Aligned the TOTP transition layout, route, and service authorization checks with the registered `glitter-social_login_totp.totp_transition.manage` permission.
- Added regression coverage for transition permission registration, authorization, and related administrator page contracts.

## 1.0.2

- Corrected expression-form translation lookups used by provider ordering and profile account controls.

## 1.0.1

- Corrected the frontend translation namespace to `glitter-social_login_totp` across settings, administration, login, registration, and profile layouts.
- Added Korean and English settings translation coverage checks for all five OAuth providers.

## 1.0.0

- Created the independent `glitter-social_login_totp` product.
- Preserved Naver, Kakao, Google, Facebook, and GitHub OAuth flows.
- Enforced mandatory TOTP enrollment for new social registrations and mandatory TOTP verification for existing social logins.
- Isolated namespaces, routes, settings, sessions, cache keys, events, logs, and database tables from the original product.
- Kept the plugin TOTP policy independent of Gnuboard7 `security.two_factor_auth`.
