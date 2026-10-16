<?php

namespace Glitter\SocialLoginTOTP\Auth;

use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Glitter\SocialLoginTOTP\Exchange\BoundExchangeService;

/**
 * Social OAuth 첫 번째 인증과 G7 Login-purpose IDV를 연결합니다.
 *
     * pending record에는 OAuth credential이나 TOTP secret을 저장하지 않고,
     * 짧은 수명의 opaque id와 User/provider/session binding만 저장합니다.
 */
final class SocialTwoFactorService
{
    private const PENDING_TTL_SECONDS = 300;

    private const LOCK_SECONDS = 10;

    private const CACHE_PREFIX = 'glitter_social_login_totp:two-factor:';

    public const ERROR_INVALID = 'social_login_two_factor_invalid';

    public function __construct(
        private readonly SocialLoginEligibility $eligibility,
        private readonly BoundExchangeService $exchange,
        private readonly SocialTotpService $totp,
    ) {}

    /**
     * @return array{pending_id: string, challenge_id: string, provider_id: string, expires_at: string}
     */
    public function start(
        User $user,
        string $provider,
        string $providerUserId,
        string $sessionBinding,
        string $redirectPath,
    ): array {
        if ($sessionBinding === '' || $provider === '' || $providerUserId === '') {
            throw new \RuntimeException(self::ERROR_INVALID);
        }

        $this->eligibility->assertBaseCanAuthenticate($user);

        if (! $this->eligibility->requiresAdditionalAuthentication($user)) {
            throw new \RuntimeException(self::ERROR_INVALID);
        }

        if (! $this->totp->isEnabled($user)) {
            $this->logStartFailure($provider, 'totp_not_registered');
            throw new \RuntimeException(self::ERROR_INVALID);
        }

        $pendingId = Str::random(48);

        $stored = Cache::put($this->key($pendingId), [
            'user_id' => $user->getKey(),
            'provider' => $provider,
            'provider_user_id_hash' => $this->identityHash($provider, $providerUserId),
            'session_binding_hash' => hash('sha256', $sessionBinding),
            'redirect_path' => $redirectPath,
            'method' => SocialTotpService::PROVIDER_ID,
        ], now()->addSeconds(self::PENDING_TTL_SECONDS));

        if (! $stored) {
            $this->logStartFailure($provider, 'pending_cache_put');
            throw new \RuntimeException(self::ERROR_INVALID);
        }

        return [
            'pending_id' => $pendingId,
            'challenge_id' => '',
            'provider_id' => SocialTotpService::PROVIDER_ID,
            'expires_at' => now()->addSeconds(self::PENDING_TTL_SECONDS)->toIso8601String(),
        ];
    }

    /**
     * 시작 실패의 원인을 운영 로그에서 단계별로 식별할 수 있게 합니다.
     * 인증 코드, OAuth 토큰, challenge ID, 세션 binding 원문과 예외 메시지는 기록하지 않습니다.
     *
     * @param array<string, scalar|null> $context
     */
    private function logStartFailure(
        string $provider,
        string $stage,
        ?\Throwable $exception = null,
        array $context = [],
    ): void {
        Log::channel('glitter_social_login_totp')->warning('glitter-social_login_totp: 추가 인증 시작 단계 실패', array_merge([
            'provider' => $provider,
            'stage' => $stage,
            'exception' => $exception ? get_class($exception) : null,
            'action' => 'two_factor_start',
        ], $context));
    }

    /**
     * @return string|null Assured one-time exchange code
     */
    public function complete(string $pendingId, string $sessionBinding, string $code): ?string
    {
        if ($pendingId === '' || $sessionBinding === '' || $code === '') {
            return null;
        }

        $key = $this->key($pendingId);
        $lock = Cache::lock($key.':lock', self::LOCK_SECONDS);

        if (! $lock->get()) {
            return null;
        }

        try {
            $pending = Cache::get($key);
            if (! is_array($pending) || ! $this->bindingMatches($pending, $sessionBinding)) {
                return null;
            }

            if (! is_numeric($pending['user_id'] ?? null)
                || ! is_string($pending['provider'] ?? null)
                || $pending['provider'] === ''
                || ! is_string($pending['provider_user_id_hash'] ?? null)
                || ! preg_match('/^[a-f0-9]{64}$/', $pending['provider_user_id_hash'])
            ) {
                Cache::forget($key);
                return null;
            }

            if (($pending['method'] ?? null) !== SocialTotpService::PROVIDER_ID) {
                Cache::forget($key);
                return null;
            }

            $verifiedUser = User::query()->find($pending['user_id']);
            if (! $verifiedUser) {
                Cache::forget($key);
                return null;
            }

            $counter = $this->totp->verifyForLogin($verifiedUser, $code);
            if ($counter === null) {
                return null;
            }

            $this->eligibility->assertBaseCanAuthenticate($verifiedUser);

            $challengeId = $this->totp->challengeReference($verifiedUser, $counter);
            $exchangeCode = $this->exchange->issueAfterSecondFactor(
                $verifiedUser,
                $sessionBinding,
                (string) ($pending['redirect_path'] ?? '/'),
                $challengeId,
                now(),
            );

            // The challenge is already verified, so a retry cannot issue a second
            // exchange even if the cache delete is interrupted.
            Cache::forget($key);

            return $exchangeCode;
        } catch (\Throwable) {
            // provider/IDV 내부 오류와 challenge 세부정보를 외부에 노출하지 않습니다.
            return null;
        } finally {
            $lock->release();
        }
    }

    private function key(string $pendingId): string
    {
        return self::CACHE_PREFIX.hash('sha256', $pendingId);
    }

    private function bindingMatches(array $pending, string $sessionBinding): bool
    {
        $expected = $pending['session_binding_hash'] ?? null;

        return is_string($expected)
            && hash_equals($expected, hash('sha256', $sessionBinding));
    }

    private function identityHash(string $provider, string $providerUserId): string
    {
        return hash('sha256', $provider."\0".$providerUserId);
    }
}
