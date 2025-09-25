<?php

namespace App\Policies;

use Illuminate\Auth\Access\Response;
use App\Models\Thread;
use App\Models\User;

class ThreadPolicy
{
    public function view(User $user, Thread $thread): bool
    {
        if ($user->role === 'coach') {
            return $user->coach && $thread->coach_id === $user->coach->id;
        }
        if ($user->role === 'client') {
            return $user->client && $thread->client_id === $user->client->id;
        }
        return $user->role === 'admin';
    }

    public function create(User $user): bool
    {
        return in_array($user->role, ['client','coach'], true);
    }

    public function reply(User $user, Thread $thread): bool
    {
        // client of coach van deze thread
        return $this->view($user, $thread);
    }
}
