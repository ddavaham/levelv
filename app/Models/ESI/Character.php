<?php

namespace LevelV\Models\ESI;

use Illuminate\Database\Eloquent\Model;

use ESIK\Models\MemberWalletTransaction;
use ESIK\Models\SDE\{Ancestry, Bloodline, Race};

class Character extends Model
{
    protected $primaryKey = 'id';
    protected $table = 'characters';
    public $incrementing = false;
    protected static $unguarded = true;

    protected $dates = [
        'birthday', 'cached_until'
    ];

    public function member()
    {
        return $this->hasOne(Member::class, 'id', 'id');
    }

    public function corporation ()
    {
        return $this->hasOne(Corporation::class, 'id', 'corporation_id');
    }

    public function history()
    {
        return $this->hasMany(CharacterCorporationHistory::class, 'id', 'id');
    }

    public function alliance ()
    {
        return $this->hasOne(Alliance::class, 'id', 'alliance_id');
    }

    public function ancestry()
    {
        return $this->hasOne(Ancestry::class, 'id', 'ancestry_id');
    }
    public function bloodline()
    {
        return $this->hasOne(Bloodline::class, 'id', 'bloodline_id');
    }
    public function race ()
    {
        return $this->hasOne(Race::class, 'id', "race_id");
    }

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

    public function sender()
    {
        return $this->morphTo(MailHeader::class, 'sender');
    }

    public function mail_recipient ()
    {
        return $this->morphOne(MailRecipient::class, 'info');
    }

    public function contact_info()
    {
        return $this->morphOne(MemberContact::class, 'info');
    }

    public function wallet_transactions_client()
    {
        return $this->morphOne(MemberWalletTransaction::class, 'client');
    }
}
