<?php

namespace App\Http\Controllers;

use App\Models\Payment;
use App\Models\Subscription;
use App\Models\Client;
use App\Services\AssignsCoach;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class CheckoutController extends Controller
{
    public function create(Request $request)
    {
        $validated = $request->validate([
            'client_id'    => ['required','exists:clients,id'],
            'period_weeks' => ['required','in:12,24'],
        ]);

        $weeks = (int) $validated['period_weeks'];
        $price = $weeks === 12 ? 29900 : 49900;

        /**
         * FAKE MODE: sla Stripe over, markeer als betaald, activeer, login & redirect.
         * Zet lokaal in .env: PAYMENTS_FAKE=true + config/payments.php => ['fake' => env('PAYMENTS_FAKE', false)]
         */
        if (config('payments.fake')) {
            $payment = Payment::create([
                'client_id'         => (int) $validated['client_id'],
                'stripe_session_id' => 'fake_' . Str::uuid(),
                'amount'            => 0,
                'currency'          => 'EUR',
                'status'            => 'paid',
                'period_weeks'      => $weeks,
            ]);

            $this->ensureSubscriptionAndActivateClient($payment, $weeks);

            return $this->goToPortalFor($payment);
        }

        /**
         * NORMALE STRIPE FLOW
         */
        \Stripe\Stripe::setApiKey(config('services.stripe.secret'));

        try {
            $session = \Stripe\Checkout\Session::create([
                'mode' => 'payment',
                'payment_method_types' => ['card','ideal'],
                'allow_promotion_codes' => true,
                'line_items' => [[
                    'quantity' => 1,
                    'price_data' => [
                        'currency'    => 'eur',
                        'unit_amount' => $price,
                        'product_data' => [
                            'name' => 'Hyrox Coaching pakket ('.$weeks.' weken)',
                        ]
                    ],
                ]],
                'metadata' => [
                    'client_id'    => (string) $validated['client_id'],
                    'period_weeks' => (string) $weeks,
                ],
                'success_url' => route('checkout.success').'?session_id={CHECKOUT_SESSION_ID}',
                'cancel_url'  => route('checkout.cancel'),
            ]);
        } catch (\Throwable $e) {
            report($e);
            return back()->withErrors(['payment' => 'Kon Stripe-sessie niet aanmaken. Probeer het later opnieuw.']);
        }

        Payment::create([
            'client_id'         => (int) $validated['client_id'],
            'stripe_session_id' => $session->id,
            'amount'            => $price,
            'currency'          => 'EUR',
            'status'            => 'pending',
            'period_weeks'      => $weeks,
        ]);

        return redirect($session->url);
    }

    public function success(Request $request)
    {
        $sessionId = $request->query('session_id');
        abort_unless($sessionId, 400, 'Missing session_id');

        \Stripe\Stripe::setApiKey(config('services.stripe.secret'));

        $session = \Stripe\Checkout\Session::retrieve($sessionId);
        $payment = Payment::where('stripe_session_id', $sessionId)->firstOrFail();

        // Al verwerkt? Meteen door naar portaal.
        if (
            $payment->status === 'paid' &&
            Subscription::where('client_id', $payment->client_id)
                ->where('period_weeks', (int) $payment->period_weeks)
                ->where('starts_at', '>=', now()->subDay())
                ->exists()
        ) {
            return $this->goToPortalFor($payment);
        }

        // Stripe bevestigt 'paid'?
        if (($session->payment_status ?? null) === 'paid') {
            $payment->update(['status' => 'paid']);

            $this->ensureSubscriptionAndActivateClient($payment, (int) $payment->period_weeks);

            return $this->goToPortalFor($payment);
        }

        // Geen betaling doorgekomen
        return view('checkout.cancel')->withErrors([
            'payment' => 'We konden je betaling niet als voltooid bevestigen. Als je wel hebt betaald, refresh deze pagina of neem contact op.'
        ]);
    }

    public function cancel()
    {
        return view('checkout.cancel');
    }

    /**
     * Zorgt dat er een actieve subscription is, activeert de client en koppelt een coach.
     */
    protected function ensureSubscriptionAndActivateClient(Payment $payment, int $weeks): void
    {
        $hasRecent = Subscription::where('client_id', $payment->client_id)
            ->where('status', 'active')
            ->where('ends_at', '>', now())
            ->exists();

        if (!$hasRecent) {
            $starts = now();
            $ends   = now()->addWeeks($weeks);

            Subscription::create([
                'client_id'    => $payment->client_id,
                'period_weeks' => $weeks,
                'starts_at'    => $starts,
                'ends_at'      => $ends,
                'status'       => 'active',
            ]);
        }

        // Client activeren & coach koppelen
        $client = Client::with('user')->find($payment->client_id);
        if ($client) {
            if ($client->status !== 'active') {
                $client->status = 'active';
                $client->save();
            }
            if (!$client->coach_id) {
                AssignsCoach::assign($client);
            }
        }
    }

    /**
     * Log de user (van de client) in en stuur naar het client-portaal.
     */
    protected function goToPortalFor(Payment $payment)
    {
        $client = \App\Models\Client::with('user')->find($payment->client_id);

        if ($client && !$client->user) {
            $this->ensureUserForClient($client);
            $client->load('user');
        }

        if ($client && $client->user) {
            \Illuminate\Support\Facades\Auth::login($client->user);

            // Als nog geen wachtwoord gezet, eerst naar set-password
            if (!$client->user->password_set_at) {
                return redirect()->route('account.password.set');
            }
        }

        return redirect()->route('client.dashboard')->with('status', 'Abonnement geactiveerd ✅');
    }

    protected function ensureUserForClient(Client $client): void
    {
        // Pak de laatste intake en lees naam/e-mail
        $intake = \App\Models\Intake::where('client_id', $client->id)->latest()->first();
        $payload = $intake?->payload ?? [];

        $email = $payload['email'] ?? null;
        $name  = trim((string)($payload['name'] ?? '')) ?: 'Client';

        if (!$email) {
            // Geen e-mail? Dan kunnen we geen login maken. Laat het stilletjes zitten.
            return;
        }

        // Bestaat user al op dit e-mailadres?
        $user = \App\Models\User::firstOrCreate(
            ['email' => $email],
            [
                'name'     => $name,
                'password' => \Illuminate\Support\Facades\Hash::make(\Illuminate\Support\Str::random(32)),
                'role'     => 'client',
            ]
        );

        if (!$client->user_id) {
            $client->user_id = $user->id;
            $client->save();
        }
    }
}
