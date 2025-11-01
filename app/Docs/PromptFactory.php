<?php

namespace App\Docs;

use App\Docs\Builders\SelfAssessmentBuilder;
use App\Docs\Builders\IsmsPolicyBuilder;
use App\Docs\Builders\CryptoPolicyBuilder;
use App\Docs\Builders\BcpDrpBuilder;
use App\Docs\Builders\AssetCatalogBuilder;

class PromptFactory
{
    public static function make(string $type, array $profile, array $meta): string
    {
        return match ($type) {
            'self_assessment' => SelfAssessmentBuilder::prompt($profile, $meta),
            'isms_policy'     => IsmsPolicyBuilder::prompt($profile, $meta),
            'crypto_policy'   => CryptoPolicyBuilder::prompt($profile, $meta),
            'bcp_drp'         => BcpDrpBuilder::prompt($profile, $meta),
            'asset_catalog'   => AssetCatalogBuilder::prompt($profile, $meta),
            default           => throw new \InvalidArgumentException("Unknown type: {$type}"),
        };
    }
}
