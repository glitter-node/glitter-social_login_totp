# Security model

OAuth is the first authentication factor. New registrations cannot create a member until TOTP enrollment and confirmation complete. Existing provider identities cannot create a login session until the TOTP challenge completes.

TOTP secrets are encrypted at rest. Validity windows, replay counters, attempt limits, lock state, recovery evidence, re-registration, transition records, and transaction boundaries remain enforced by the dedicated services and tables. Email equality does not link accounts, and a provider identity already owned by another member is rejected.

The plugin does not read Gnuboard7 `security.two_factor_auth`. Its mandatory social TOTP policy is the same when the core setting is true or false. Diagnostic fields are limited to provider, action, stage, reason, and exception class.
