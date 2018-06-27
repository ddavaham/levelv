<?php

namespace LevelV\Models\ESI;

use ESIK\Models\MemberWalletTransaction;
use Illuminate\Database\Eloquent\Model;

class Corporation extends Model
{
    protected $primaryKey = 'id';
    protected $table = 'corporations';
    public $incrementing = false;
    protected static $unguarded = true;

    public function creator()
    {
        return $this->morphOn(MemberBookmark::class, 'creator');
    }

    public function assignee()
    {
        return $this->morphOne(Contract::class, 'assignee');
    }

    public function acceptor()
    {
        return $this->morphOne(Contract::class, 'acceptor');
    }

    public function wallet_transactions_client()
    {
        return $this->morphOne(MemberWalletTransaction::class, 'client');
    }

    public function contact_info()
    {
        return $this->morphOne(MemberContact::class, 'info');
    }

    public function mail_recipient ()
    {
        return $this->morphOne(MailRecipient::class, 'info');
    }
}
