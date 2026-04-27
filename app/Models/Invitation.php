<?php

namespace App\Models;

use App\Casts\AsSqid;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\URL;
use App\Enums\Role;
use App\Notifications\InvitationForExistingAccount;
use App\Notifications\InvitationForNewAccount;

class Invitation extends Model
{
    use HasFactory;
    use Traits\HasSqid;

    protected $fillable = [
        'email',
        'project_id',
        'role',
    ];

    protected function casts(): array
    {
        return [
            'account_sqid' => AsSqid::class,
            'project_sqid' => AsSqid::class,
            'role' => Role::class,
        ];
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

    /* Attributes */

    public function url(): Attribute
    {
        return new Attribute(fn() => URL::signedRoute('invitations.accept', $this));
    }
}
