<?php

namespace App\Providers;

use App\Models\TrainingPlan;
use App\Models\Thread;
use App\Policies\TrainingPlanPolicy;
use App\Policies\ThreadPolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;

class AuthServiceProvider extends ServiceProvider
{
    // Optioneel: klassiek $policies = [...]
    // protected $policies = [
    //     TrainingPlan::class => TrainingPlanPolicy::class,
    //     Thread::class       => ThreadPolicy::class,
    // ];

    public function boot(): void
    {
        Gate::policy(TrainingPlan::class, TrainingPlanPolicy::class);
        Gate::policy(Thread::class,       ThreadPolicy::class);

        // of: $this->registerPolicies();
    }
}
