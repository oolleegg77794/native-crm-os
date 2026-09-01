<?php

declare(strict_types=1);

namespace App\Core\Timeline;

use App\Models\CrmRecord;
use App\Models\RecordTimeline;
use App\Models\User;

class ActivityLogger
{
    public function logCreate(CrmRecord $record, ?User $user = null): RecordTimeline
    {
        return RecordTimeline::create([
            'record_id' => $record->id,
            'module_id' => $record->module_id,
            'user_id' => $user?->id,
            'event_type' => 'create',
            'changes' => [
                'new' => $record->data,
            ],
            'description' => 'Record created',
        ]);
    }

    public function logUpdate(CrmRecord $record, array $oldData, ?User $user = null): ?RecordTimeline
    {
        $newData = $record->data ?? [];
        $diffOld = [];
        $diffNew = [];

        foreach ($newData as $key => $newValue) {
            $oldValue = $oldData[$key] ?? null;
            if ($oldValue !== $newValue) {
                $diffOld[$key] = $oldValue;
                $diffNew[$key] = $newValue;
            }
        }

        // Also check removed keys
        foreach ($oldData as $key => $oldValue) {
            if (! array_key_exists($key, $newData) && $oldValue !== null) {
                $diffOld[$key] = $oldValue;
                $diffNew[$key] = null;
            }
        }

        if (empty($diffOld) && empty($diffNew)) {
            return null;
        }

        return RecordTimeline::create([
            'record_id' => $record->id,
            'module_id' => $record->module_id,
            'user_id' => $user?->id,
            'event_type' => 'update',
            'changes' => [
                'old' => $diffOld,
                'new' => $diffNew,
            ],
            'description' => 'Record updated',
        ]);
    }

    public function logDelete(CrmRecord $record, ?User $user = null): RecordTimeline
    {
        return RecordTimeline::create([
            'record_id' => $record->id,
            'module_id' => $record->module_id,
            'user_id' => $user?->id,
            'event_type' => 'delete',
            'changes' => null,
            'description' => 'Record deleted',
        ]);
    }

    public function logOwnerChange(CrmRecord $record, int $oldOwnerId, int $newOwnerId, ?User $user = null): RecordTimeline
    {
        return RecordTimeline::create([
            'record_id' => $record->id,
            'module_id' => $record->module_id,
            'user_id' => $user?->id,
            'event_type' => 'change_owner',
            'changes' => [
                'old_owner_id' => $oldOwnerId,
                'new_owner_id' => $newOwnerId,
            ],
            'description' => 'Record owner changed',
        ]);
    }
}
