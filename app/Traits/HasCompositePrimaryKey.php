<?php

namespace LevelV\Traits;

use Illuminate\Database\Eloquent\Builder;

trait HasCompositePrimaryKey
{
    /**
     * @return bool
     */
    public function getIncrementing()
    {
        return false;
    }
    /**
     * Sadly, composite primary keys in Eloquent does not seem to
     * be a *thing*. This override allowes for things like firstOrUpdate()
     * to work. However, many other eloquent static methods dont work with
     * composite keys. ¯\_(ツ)_/¯.
     *
     * Monkey patch refs:
     *  https://github.com/laravel/framework/issues/5517#issuecomment-113655441
     *  https://github.com/laravel/framework/issues/5355#issuecomment-161376267
     *  https://github.com/warlof/eveseat-mining-ledger/blob/a03e15354d00567db46ec883a1e803824350c26b/src/Models/Character/MiningJournal.php#L46-L66
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    protected function setKeysForSaveQuery(Builder $query)
    {
        if (is_array($this->getKeyName())) {
            foreach ((array) $this->getKeyName() as $keyField) {
                $query->where($keyField, '=', $this->original[$keyField]);
            }
            return $query;
        }
        return parent::setKeysForSaveQuery($query);
    }
}
