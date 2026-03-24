<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AffiliateClick extends Model
{
    protected $fillable = [
        'gift_id',
        'list_id',
        'url',
        'affiliate_url',
        'retailer_domain',
        'ip_hash',
        'user_agent',
        'clicked_at',
    ];

    protected function casts(): array
    {
        return [
            'clicked_at' => 'datetime',
        ];
    }

    /**
     * @return BelongsTo<Gift, $this>
     */
    public function gift(): BelongsTo
    {
        return $this->belongsTo(Gift::class);
    }

    /**
     * @return BelongsTo<GiftList, $this>
     */
    public function giftList(): BelongsTo
    {
        return $this->belongsTo(GiftList::class, 'list_id');
    }
}
