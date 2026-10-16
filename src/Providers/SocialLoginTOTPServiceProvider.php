<?php

namespace Glitter\SocialLoginTOTP\Providers;

use App\Extension\BasePluginServiceProvider;
use Glitter\SocialLoginTOTP\Support\SocialLoginRateLimiters;

final class SocialLoginTOTPServiceProvider extends BasePluginServiceProvider
{
    protected string $pluginIdentifier = 'glitter-social_login_totp';

    public function register(): void
    {
        parent::register();
        $logging = require __DIR__.'/../../config/logging.php';
        $channel = $logging['channels']['glitter_social_login_totp'] ?? null;
        if (! is_array($channel)) {
            throw new \RuntimeException('Invalid glitter-social_login_totp logging channel configuration.');
        }
        $this->app->make('config')->set('logging.channels.glitter_social_login_totp', $channel);
    }

    public function boot(): void
    {
        parent::boot();

        SocialLoginRateLimiters::register();
    }
}

namespace Plugins\Glitter\SocialLoginTotp\Providers;

use App\Extension\BasePluginServiceProvider;
use Glitter\SocialLoginTOTP\Support\SocialLoginRateLimiters;

final class SocialLoginTOTPServiceProvider extends BasePluginServiceProvider
{
    protected string $pluginIdentifier = 'glitter-social_login_totp';

    public function register(): void
    {
        parent::register();
        $logging = require __DIR__.'/../../config/logging.php';
        $channel = $logging['channels']['glitter_social_login_totp'] ?? null;
        if (! is_array($channel)) {
            throw new \RuntimeException('Invalid glitter-social_login_totp logging channel configuration.');
        }
        $this->app->make('config')->set('logging.channels.glitter_social_login_totp', $channel);
    }

    public function boot(): void
    {
        parent::boot();

        SocialLoginRateLimiters::register();
    }
}
