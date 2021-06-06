<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property int $broadcast_id
 * @property integer $user_id
 * @property string $joined_on
 * @property string $last_viewed_on
 * @property Broadcast $broadcast
 * @property User $user
 */
class BroadcastViewer extends Model
{
    /**
     * @var array
     */
    protected $fillable = ['broadcast_id', 'user_id', 'joined_on', 'last_viewed_on'];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function broadcast()
    {
        return $this->belongsTo('App\Broadcast', null, 'broadcast_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user()
    {
        return $this->belongsTo('App\User');
    }
}
