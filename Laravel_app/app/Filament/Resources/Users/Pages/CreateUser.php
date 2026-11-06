<?php

namespace App\Filament\Resources\Users\Pages;

use App\Filament\Resources\Users\UserResource;
use App\Support\UserEmailVerification;
use App\Support\UserRoleAssignments;
use Filament\Facades\Filament;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class CreateUser extends CreateRecord
{
    protected static string $resource = UserResource::class;

    protected function handleRecordCreation(array $data): Model
    {
        return DB::transaction(function () use ($data): Model {
            $role = app(UserRoleAssignments::class)->resolve(Filament::auth()->user(), $data['role_id'] ?? null);
            unset($data['role_id']);

            $user = parent::handleRecordCreation($data);
            $user->syncRoles($role);

            return $user;
        });
    }

    protected function afterCreate(): void
    {
        $user = $this->record;

        if ($user->hasVerifiedEmail()) {
            return;
        }

        app(UserEmailVerification::class)->send($user);

        Notification::make()
            ->title('Email de verificación enviado a ' . $user->email)
            ->success()
            ->send();
    }
}
