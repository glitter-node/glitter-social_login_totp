<?php

namespace Glitter\SocialLoginTOTP\Upgrades;

use App\Extension\AbstractUpgradeStep;

/**
 * The 1.5.3 schema migration is additive and performs no data backfill.
 * This step keeps the plugin update lifecycle explicit for the schema change.
 */
class Upgrade_1_5_3 extends AbstractUpgradeStep {}
