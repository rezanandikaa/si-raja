<?php

namespace App\Observers;

use Illuminate\Database\Eloquent\Model;

class AuditTrailObserver
{
    // retrieved : after a record has been retrieved.
    // creating : before a record has been created.
    // created : after a record has been created.
    // updating : before a record is updated.
    // updated : after a record has been updated.
    // saving : before a record is saved (either created or updated).
    // saved : after a record has been saved (either created or updated).
    // deleting : before a record is deleted or soft-deleted.
    // deleted : after a record has been deleted or soft-deleted.
    // restoring : before a soft-deleted record is going to be restored.
    // restored : after a soft-deleted record has been restored.


    public function creating(Model $model)
    {
        $model->created_by_id = auth()->id() ?? 0;
        $model->updated_by_id = auth()->id() ?? 0;
    }

    /**
     * Handle the user "created" event.
     *
     * @param  \App\User  $user
     * @return void
     */
    public function created(Model $model)
    {

    }

    public function updating(Model $model)
    {
        $model->updated_by_id = auth()->id() ?? 0;
    }

    /**
     * Handle the user "updated" event.
     *
     * @param  \App\User  $user
     * @return void
     */
    public function updated(Model $model)
    {
        //
    }

    /**
     * Handle the user "deleted" event.
     *
     * @param  \App\User  $user
     * @return void
     */
    public function deleted(Model $model)
    {
        //
    }

    // /**
    //  * Handle the user "restored" event.
    //  *
    //  * @param  \App\User  $user
    //  * @return void
    //  */
    // public function restored(User $user)
    // {
    //     //
    // }

    // /**
    //  * Handle the user "force deleted" event.
    //  *
    //  * @param  \App\User  $user
    //  * @return void
    //  */
    // public function forceDeleted(User $user)
    // {
    //     //
    // }
}
