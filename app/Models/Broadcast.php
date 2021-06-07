<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

/**
 * @property int $broadcast_id
 * @property integer $user_id
 * @property string $started_on
 * @property string $ended_on
 * @property User $user
 * @property BroadcastViewer[] $broadcastViewers
 */
class Broadcast extends Model
{
    /**
     * The primary key for the model.
     *
     * @var string
     */
    protected $primaryKey = 'broadcast_id';

    public $timestamps = false;

    /**
     * @var array
     */
    protected $fillable = ['user_id', 'started_on', 'ended_on'];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user()
    {
        return $this->belongsTo('App\User');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function broadcastViewers()
    {
        return $this->hasMany('App\BroadcastViewer', null, 'broadcast_id');
    }
}
