<?php

namespace App\Observers;

use App\Models\Member;
use App\Models\MemberStatusHistory;
use App\Models\MemberProgressHistory;

class MemberObserver
{
    /**
     * Handle the Member "created" event.
     */
    public function created(Member $member): void
    {
        //
    }

    /**
     * Handle the Member "updated" event.
     */
    public function updated(Member $member): void
    {
        //
    }

    public function updating(Member $member)
    {
        // 🔥 ステータス変更
        if ($member->isDirty('status_id')) {
            MemberStatusHistory::create([
                'member_id' => $member->id,
                'status_id' => $member->status_id,
                'changed_by' => auth()->id(),
            ]);
        }

        // 🔥 進捗変更
        if ($member->isDirty('progress_id')) {
            MemberProgressHistory::create([
                'member_id' => $member->id,
                'progress_id' => $member->progress_id,
                'changed_by' => auth()->id(),
            ]);
        }
    }
    /**
     * Handle the Member "deleted" event.
     */
    public function deleted(Member $member): void
    {
        //
    }

    /**
     * Handle the Member "restored" event.
     */
    public function restored(Member $member): void
    {
        //
    }

    /**
     * Handle the Member "force deleted" event.
     */
    public function forceDeleted(Member $member): void
    {
        //
    }
}
