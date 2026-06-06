<?php

namespace App\Traits;

use App\Models\AuditLog;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;

trait Auditable
{
    protected static function bootAuditable()
    {
        static::created(function ($model) {
            static::logAction('created', $model, null, $model->toArray());
        });

        static::updated(function ($model) {
            $changes = $model->getDirty();
            $old = [];
            $new = [];
            foreach ($changes as $key => $value) {
                if (in_array($key, ['updated_at', 'created_at'])) {
                    continue;
                }
                $old[$key] = $model->getOriginal($key);
                $new[$key] = $value;
            }
            if (! empty($old)) {
                static::logAction('updated', $model, $old, $new);
            }
        });

        static::deleted(function ($model) {
            static::logAction('deleted', $model, $model->toArray(), null);
        });
    }

    protected static function logAction(string $action, $model, $old = null, $new = null)
    {
        if (! Auth::check()) {
            return;
        }

        AuditLog::create([
            'user_id' => Auth::id(),
            'action' => $action,
            'entity_type' => get_class($model),
            'entity_id' => $model->id,
            'previous_values' => $old,
            'new_values' => $new,
            'ip_address' => Request::ip(),
            'user_agent' => Request::userAgent(),
        ]);
    }
}
