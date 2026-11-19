<?php

namespace App\Support;

use App\Models\Customer;
use App\Notifications\ResetCustomerPassword;
use Illuminate\Auth\Events\PasswordResetLinkSent;
use Illuminate\Auth\Passwords\PasswordBroker;
use Illuminate\Contracts\Foundation\Application;

class CustomerPasswordBroker
{
    private PasswordBroker $broker;

    public function __construct(Application $app)
    {
        $config = $app['config']['auth.passwords.customers'];
        $key = $app['config']['app.key'];
        if (str_starts_with($key, 'base64:')) {
            $key = base64_decode(substr($key, 7));
        }

        $this->broker = new PasswordBroker(
            new CustomerResetTokenRepository(
                $app['db']->connection($config['connection'] ?? null),
                $app['hash'],
                $config['table'],
                $key,
                ($config['expire'] ?? 60) * 60,
                $config['throttle'] ?? 0,
            ),
            $app['auth']->createUserProvider($config['provider']),
            $app['events'],
            timeboxDuration: $app['config']->get('auth.timebox_duration', 200000),
        );
    }

    public function sendResetLink(array $credentials): string
    {
        return $this->broker->sendResetLink($credentials, function (Customer $customer, string $token): void {
            $customer->notify(new ResetCustomerPassword($token));
            event(new PasswordResetLinkSent($customer));
        });
    }

    public function reset(array $credentials, \Closure $callback): string
    {
        return $this->broker->reset($credentials, $callback);
    }
}
