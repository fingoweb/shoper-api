<?php

namespace App\Services;

use App\Models\Shop;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class OnetAdsService
{
    private const ONET_API_URL = 'https://csr.onet.pl/1551662/tags';
    private static array $itemsToRemoveFromShopDomain = ['https://', 'http://', '/'];
    private const TPL_CODE_IF_NOT_EXISTS_OR_INACTIVE = 'lps/RMN';
    private static array $statusesInactive = ['inactive', 'deactivated'];
    private const STATUS_ACTIVE = 'active';

    private const SHOP_DOESNT_EXISTS = 0;
    private const SHOP_IS_INACTIVE = 1;
    private const SHOP_IS_ACTIVE = 2;

    public function __construct(public Shop $shop)
    {
    }

    public function onetAdsStatus(): int
    {
        $domain = Str::replace(self::$itemsToRemoveFromShopDomain, '', $this->shop->shop_url);
        $formattedDomain = self::formatDomainForOnetAds($domain);
        $response = Http::get(self::ONET_API_URL, [
            'domain' => $domain,
            'site' => $formattedDomain
        ])->body();

        $status = self::checkOnetAdsStatus($response);

        return ($status);
    }

    private static function formatDomainForOnetAds(string $domain): string
    {
        return preg_replace('/[^\w-]+/', '_', $domain);
    }

    private static function checkOnetAdsStatus(string $data): int
    {
        $data = json_decode($data);

        if (self::shopDoesntExists($data)) {
            return self::SHOP_DOESNT_EXISTS;
        }

        if (self::shopIsInactive($data)) {
            return self::SHOP_IS_INACTIVE;
        }
        if (self::shopIsActive($data)) {
            return self::SHOP_IS_ACTIVE;
        }

        return self::SHOP_DOESNT_EXISTS;
    }

    private static function shopDoesntExists(\stdClass $data): bool
    {
        if (!$data->tags || !$data->tags->page_context) {
            return true;
        }

        foreach ($data->tags->page_context as $item) {
            if ($item->data->tplCode !== self::TPL_CODE_IF_NOT_EXISTS_OR_INACTIVE) {
                return true;
            }
        }

        return false;
    }

    private static function shopIsInactive(\stdClass $data): bool
    {
        foreach ($data->tags->page_context as $item) {
            if ($item->data->tplCode === self::TPL_CODE_IF_NOT_EXISTS_OR_INACTIVE
                && in_array($item->data->fields->status, self::$statusesInactive)
            ) {
                return true;
            }
        }
        return false;
    }

    private static function shopIsActive(\stdClass $data): bool
    {
        foreach ($data->tags->page_context as $item) {
            if ($item->data->tplCode === self::TPL_CODE_IF_NOT_EXISTS_OR_INACTIVE
                && $item->data->tplCode->status === self::STATUS_ACTIVE) {
                return true;
            }
        }
        return false;
    }
}
