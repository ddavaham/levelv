<?php

namespace LevelV\Models\ESI;

use Illuminate\Database\Eloquent\Model;

class Alliance extends Model
{
    protected $primaryKey = 'id';
    protected $table = 'alliances';
    public $incrementing = false;
    protected static $unguarded = true;

    public function assignee()
    {
        return $this->morphOne(Contract::class, 'assignee');
    }

    public function acceptor()
    {
        return $this->morphOne(Contract::class, 'acceptor');
    }

    public function mail_recipient ()
    {
        return $this->morphOne(MailRecipient::class, 'info');
    }

    public function contact_info()
    {
        return $this->morphOne(MemberContact::class, 'info');
    }
}
