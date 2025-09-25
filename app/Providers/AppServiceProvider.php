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
    /**
     * Als je de klassieke mapping wilt gebruiken:
     * protected $policies = [
     *     TrainingPlan::class => TrainingPlanPolicy::class,
     *     Thread::class       => ThreadPolicy::class,
     * ];
     */

    public function boot(): void
    {
        // Optie A: via Gate::policy (expliciet)
        Gate::policy(TrainingPlan::class, TrainingPlanPolicy::class);
        Gate::policy(Thread::class,       ThreadPolicy::class);

        // Optie B: (alternatief) klassieke mapping gebruiken:
        // $this->registerPolicies();
    }

    public function register(): void
    {
        // hier niets voor policies
    }
}
