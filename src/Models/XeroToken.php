<?php

namespace Dcblogdev\Xero\Models;

use DateTimeInterface;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;

class XeroToken extends Model
{
    protected $guarded = [];

    /**
     * @return \Illuminate\Database\Eloquent\Casts\Attribute<DateTimeInterface, never>
     */
    protected function expires(): Attribute
    {
        return Attribute::get(
            fn(): DateTimeInterface => $this->updated_at->addSeconds($this->expires_in)
        );
    }
}
