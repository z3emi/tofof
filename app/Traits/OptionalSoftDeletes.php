<?php

namespace App\Traits;

use Illuminate\Database\Eloquent\SoftDeletes;

trait OptionalSoftDeletes
{
    use SoftDeletes {
        SoftDeletes::restore as private softRestore;
        SoftDeletes::forceDelete as private softForceDelete;
    }

    public function usesSoftDeletes(): bool
    {
        return in_array(SoftDeletes::class, class_uses_recursive(static::class));
    }

    public function delete()
    {
        if ($this->usesSoftDeletes()) {
            return parent::delete();
        }

        return parent::forceDelete();
    }

    public function restore()
    {
        if ($this->usesSoftDeletes()) {
            return $this->softRestore();
        }

        return false;
    }

    public function forceDelete()
    {
        if ($this->usesSoftDeletes()) {
            return $this->softForceDelete();
        }

        return parent::delete();
    }
}
