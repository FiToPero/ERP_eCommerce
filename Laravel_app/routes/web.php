<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/admin-user/verify-email/{id}/{hash}', function (string $id, string $hash) {
    $user = \App\Models\User::findOrFail($id);

    abort_unless(hash_equals(sha1($user->getEmailForVerification()), $hash), 403);

    if (! $user->hasVerifiedEmail()) {
        $user->markEmailAsVerified();
    }

    return redirect('/admin/login');
})->middleware(['signed', 'throttle:6,1'])->name('admin-user.verify-email');
