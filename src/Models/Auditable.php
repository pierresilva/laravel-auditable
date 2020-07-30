<?php

namespace pierresilva\Auditable;

use Illuminate\Database\Eloquent\Model;
use pierresilva\Auditable\Traits\AuditableTrait;

class Auditable extends Model
{
    use AuditableTrait;

    /**
     * The revisions table.
     *
     * @var string
     */
    protected $table = 'auditable_log';

    /**
     * The belongs to user relationship.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user()
    {
        return $this->belongsTo(\App\User::class);
    }


    public function getOldValueAttribute($value)
    {
        return json_decode($value);
    }

    public function getNewValueAttribute($value)
    {
        return json_decode($value);
    }
}
