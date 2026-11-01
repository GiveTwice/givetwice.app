<?php

namespace App\Helpers;

use App\Models\GiftList;

class ListShareHelper
{
    public static function whatsappUrl(GiftList $list): string
    {
        return 'https://wa.me/?text='.rawurlencode(__('You can view my wish list here:').' '.$list->getPublicUrl());
    }
}
