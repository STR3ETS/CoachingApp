<?php

namespace App\View\Components\Plan;

use App\Models\TrainingPlan;
use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class Viewer extends Component
{
    public function __construct(public TrainingPlan $plan) {}

    public function render(): View|Closure|string
    {
        // plan_json verwacht array: week_X => [ 'focus' => ..., 'sessions' => [ ... ] ]
        $weeks = is_array($this->plan->plan_json) ? $this->plan->plan_json : [];

        // Sorteer week_1..week_N op nummer
        uksort($weeks, function($a,$b){
            $na = (int) preg_replace('/\D+/', '', $a);
            $nb = (int) preg_replace('/\D+/', '', $b);
            return $na <=> $nb;
        });

        return view('components.plan.viewer', [
            'weeks' => $weeks,
            'title' => $this->plan->title,
            'isFinal' => (bool) $this->plan->is_final,
            'weeksCount' => (int) $this->plan->weeks,
        ]);
    }
}
