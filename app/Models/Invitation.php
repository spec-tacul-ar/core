<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Prunable;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\URL;
use App\Enums\Role;
use App\Notifications\InvitationForExistingAccount;
use App\Notifications\InvitationForNewAccount;

class Invitation extends Model
{
    use HasFactory;
    use Prunable;

    protected $fillable = [
        'email',
        'project_id',
        'role',
    ];

    protected function casts(): array
    {
        return [
            'role' => Role::class,
        ];
    }

    public function prunable(): Builder
    {
        return static::where('created_at', '<=', now()->subWeek());
    }

    /* Relations */

    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    public function account()
    {
        return $this->belongsTo(Account::class);
    }

    /* Accessors and mutators */

    public function url(): Attribute
    {
        return new Attribute(fn () => URL::signedRoute('invitations.show', $this));
    }

    /* Helpers */

    public function sendNotification()
    {
        $account = Account::findByEmail($this->email);

        if ($account) {
            $account->notify(new InvitationForExistingAccount($this));
        } else {
            Notification::route('mail', $this->email)->notify(new InvitationForNewAccount($this));
        }
    }
}
