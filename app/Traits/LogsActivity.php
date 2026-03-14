<?php

namespace App\Traits;

use App\Models\ActivityLog;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Model;

trait LogsActivity
{
    /**
     * Boot the trait.
     */
    protected static function bootLogsActivity()
    {
        static::created(function (Model $model) {
            static::logActivity($model, 'created');
        });

        static::updated(function (Model $model) {
            static::logActivity($model, 'updated');
        });

        static::deleted(function (Model $model) {
            static::logActivity($model, 'deleted');
        });
    }

    /**
     * Get the changes for an update operation.
     *
     * @return array
     */
    public function getChanges()
    {
        $changes = [];
        foreach ($this->getDirty() as $key => $value) {
            $original = $this->getOriginal($key);
            $changes[$key] = [
                'old' => $original,
                'new' => $value,
            ];
        }
        return $changes;
    }

    /**
     * Log the activity for the model.
     *
     * @param Model $model
     * @param string $action
     */
    protected static function logActivity(Model $model, string $action)
    {
        $userId = Auth::check() ? Auth::id() : null;

        ActivityLog::create([
            'user_id'       => $userId,
            'loggable_id'   => $model->id,
            'loggable_type' => get_class($model),
            'action'        => $action,
            'before'        => $action === 'updated' ? $model->getOriginal() : null,
            'after'         => $action === 'updated' ? $model->getChanges() : ($action === 'created' ? $model->getAttributes() : null),
            'ip_address'    => request()->ip(),
            'user_agent'    => request()->header('User-Agent'),
        ]);
    }
}
