<?php

namespace App\Models;

use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Model;

class LogReply extends Model
{
    use Auditable;

    protected $fillable = [
        'work_log_id',
        'user_id',
        'reply',
    ];

    public function workLog()
    {
        return $this->belongsTo(WorkLog::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
