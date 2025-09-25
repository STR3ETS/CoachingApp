<?php

namespace App\Policies;

use Illuminate\Auth\Access\Response;
use App\Models\TrainingPlan;
use App\Models\User;

class TrainingPlanPolicy
{
    public function view(User $user, TrainingPlan $plan): bool
    {
        if ($user->role === 'coach') {
            return $user->coach && $plan->coach_id === $user->coach->id;
        }
        if ($user->role === 'client') {
            return $user->client && $plan->client_id === $user->client->id;
        }
        return $user->role === 'admin';
    }

    public function create(User $user): bool
    {
        return $user->role === 'coach';
    }

    public function update(User $user, TrainingPlan $plan): bool
    {
        return $user->role === 'coach' && $user->coach && $plan->coach_id === $user->coach->id;
    }
}
