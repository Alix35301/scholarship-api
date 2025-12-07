<?php

namespace App\Services;

use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Models\Activity;
use Spatie\Activitylog\Traits\LogsActivity;

class ActivityLogService
{
    /**
     * Log an activity for a model
     *
     * @param Model $model
     * @param string $description
     * @param array $properties
     * @param Model|null $causer
     * @return Activity
     */
    public function log(Model $model, string $description, array $properties = [], ?Model $causer = null): Activity
    {
        return activity()
            ->performedOn($model)
            ->causedBy($causer ?? auth()->user())
            ->withProperties($properties)
            ->log($description);
    }

    /**
     * Get activity logs for a model
     *
     * @param Model $model
     * @param int|null $limit
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getLogsForModel(Model $model, ?int $limit = null)
    {
        $query = Activity::where('subject_type', get_class($model))
            ->where('subject_id', $model->id)
            ->with('causer')
            ->latest();

        if ($limit) {
            $query->limit($limit);
        }

        return $query->get();
    }

    /**
     * Get paginated activity logs for a model
     *
     * @param Model $model
     * @param int $perPage
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public function getPaginatedLogsForModel(Model $model, int $perPage = 15)
    {
        return Activity::where('subject_type', get_class($model))
            ->where('subject_id', $model->id)
            ->with('causer')
            ->latest()
            ->paginate($perPage);
    }
}

