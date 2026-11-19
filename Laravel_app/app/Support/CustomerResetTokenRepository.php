<?php

namespace App\Support;

use App\Models\Customer;
use Illuminate\Auth\Passwords\DatabaseTokenRepository;
use Illuminate\Contracts\Auth\CanResetPassword;
use LogicException;

class CustomerResetTokenRepository extends DatabaseTokenRepository
{
    public function create(CanResetPassword $user)
    {
        $this->deleteExisting($user);
        $token = $this->createNewToken();
        $this->getTable()->insert($this->getPayload($this->customerKey($user), $token));

        return $token;
    }

    protected function deleteExisting(CanResetPassword $user)
    {
        return $this->getTable()->where('email', $this->customerKey($user))->delete();
    }

    public function exists(CanResetPassword $user, #[\SensitiveParameter] $token)
    {
        $record = (array) $this->getTable()->where('email', $this->customerKey($user))->first();

        return $record && ! $this->tokenExpired($record['created_at'])
            && $this->hasher->check($token, $record['token']);
    }

    public function recentlyCreatedToken(CanResetPassword $user)
    {
        $record = (array) $this->getTable()->where('email', $this->customerKey($user))->first();

        return $record && $this->tokenRecentlyCreated($record['created_at']);
    }

    private function customerKey(CanResetPassword $user): string
    {
        if (! $user instanceof Customer) {
            throw new LogicException('Customer reset tokens require a customer.');
        }

        return 'customer:'.$user->getKey().':'.hash('sha256', $user->email."\0".$user->web);
    }
}
