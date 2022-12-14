<?php

namespace Dcblogdev\Xero\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;

class XeroToken extends Model
{
    protected $guarded = [];

    /**
     * @return \Illuminate\Database\Eloquent\Casts\Attribute<Carbon, never>
     */
    protected function expires(): Attribute
    {
        return Attribute::get(
            fn(): Carbon => $this->updated_at->addSeconds($this->expires_in)
        );
    }
}
