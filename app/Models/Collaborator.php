<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Collaborator extends Model
{
    const BASE_CODE_PREFIX = 1000000000;

    const DISCOUNT_ATTRIBUTE = 'discount';
    const COMMISSION_ATTRIBUTE = 'commission';
    const REVENUE_ATTRIBUTE = 'revenue';
    const RERANK_TIME_ATTRIBUTE = 'rerank_time';
    const COMMISSION_DOWNLINE_ATTRIBUTE = 'commission_downline';
    const DISCOUNT_DOWNLINE_SET_ATTRIBUTE = 'discount_downline_set';
    const COMMISSION_DOWNLINE_SET_ATTRIBUTE = 'commission_downline_set';

    const STATUS_PENDING_DB = 2;
    const STATUS_ACTIVE_DB = 1;
    const STATUS_INACTIVE_DB = 0;

    const STATUS_PENDING_LABEL = 'Chờ Duyệt';
    const STATUS_ACTIVE_LABEL = 'Hợp Tác';
    const STATUS_INACTIVE_LABEL = 'Không Hợp Tác';

    protected $table = 'collaborator';

    public $timestamps = false;

    public function user()
    {
        return $this->belongsTo('App\Models\User', 'user_id');
    }

    public function parentCollaborator()
    {
        return $this->belongsTo('App\Models\Collaborator', 'parent_id');
    }

    public function rank()
    {
        return $this->belongsTo('App\Models\Setting', 'rank_id');
    }

    public static function getCollaboratorStatus($value = null, $admin = true)
    {
        $status = [
            self::STATUS_ACTIVE_DB => self::STATUS_ACTIVE_LABEL,
            self::STATUS_INACTIVE_DB => self::STATUS_INACTIVE_LABEL,
        ];

        if($admin == true)
            $status[self::STATUS_PENDING_DB] = self::STATUS_PENDING_LABEL;

        if($value !== null && isset($status[$value]))
            return $status[$value];

        return $status;
    }

    public static function getCollaboratorRank($value = null)
    {
        $settings = Setting::getSettings(Setting::CATEGORY_COLLABORATOR_DB);

        $rank = [
            $settings[Setting::COLLABORATOR_SILVER]->id => $settings[Setting::COLLABORATOR_SILVER]->name,
            $settings[Setting::COLLABORATOR_GOLD]->id => $settings[Setting::COLLABORATOR_GOLD]->name,
            $settings[Setting::COLLABORATOR_DIAMOND]->id => $settings[Setting::COLLABORATOR_DIAMOND]->name,
            $settings[Setting::COLLABORATOR_MANAGER]->id => $settings[Setting::COLLABORATOR_MANAGER]->name,
        ];

        if($value !== null && isset($rank[$value]))
            return $rank[$value];

        return $rank;
    }

    public static function countTotalCollaborators()
    {
        return Collaborator::count('id');
    }
}