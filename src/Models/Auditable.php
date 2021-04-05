<?php

namespace pierresilva\Auditable\Models;

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

    protected $fillable = [
        'key', 'user_id'
    ];

    /**
     * The belongs to user relationship.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user()
    {
        return $this->belongsTo(\App\User::class);
    }


    /**
     * @param $value
     * @return mixed
     */
    public function getOldValueAttribute($value)
    {
        return json_decode($value);
    }

    /**
     * @param $value
     * @return mixed
     */
    public function getNewValueAttribute($value)
    {
        return json_decode($value);
    }

    /**
     * @param $value
     */
    public static function log($value)
    {
        self::create([
            'key' => $value,
            'user_id' => auth()->user()->id ?? null
        ]);
    }

    /**
     * @param int $quantity
     * @return \Illuminate\Database\Eloquent\Builder[]|\Illuminate\Database\Eloquent\Collection
     */
    public static function latestSimpleLogs($quantity = 100)
    {
        return self::with('user')
            ->where('auditable_type', null)
            ->limit($quantity)
            ->orderBy('created_at', 'DESC')
            ->get();
    }

    /**
     * @param int $quantity
     * @return \Illuminate\Database\Eloquent\Builder[]|\Illuminate\Database\Eloquent\Collection
     */
    public static function latestAudits($quantity = 100)
    {
        return self::with('user')
            ->where('auditable_type', '<>', null)
            ->limit($quantity)
            ->orderBy('created_at', 'DESC')
            ->get();
    }
}
