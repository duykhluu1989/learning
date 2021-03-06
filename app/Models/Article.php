<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Article extends Model
{
    const TYPE_EXPERT_ARTICLE_DB = 0;
    const TYPE_STATIC_ARTICLE_DB = 1;

    const STATIC_ARTICLE_GROUP_INTRO_DB = 0;
    const STATIC_ARTICLE_GROUP_GUIDE_DB = 1;
    const STATIC_ARTICLE_GROUP_COLLABORATOR_DB = 2;
    const STATIC_ARTICLE_GROUP_FAQ_DB = 3;
    const STATIC_ARTICLE_GROUP_TEACHER_DB = 4;
    const STATIC_ARTICLE_GROUP_INTRO_LABEL = 'Giới Thiệu';
    const STATIC_ARTICLE_GROUP_GUIDE_LABEL = 'Hướng Dẫn';
    const STATIC_ARTICLE_GROUP_COLLABORATOR_LABEL = 'Cộng Tác Viên';
    const STATIC_ARTICLE_GROUP_FAQ_LABEL = 'Hỏi Đáp';
    const STATIC_ARTICLE_GROUP_TEACHER_LABEL = 'Hợp Tác Giảng Dạy';

    protected $table = 'article';

    public $timestamps = false;

    public function user()
    {
        return $this->belongsTo('App\Models\User', 'user_id');
    }

    public static function getStaticArticleGroup($value = null)
    {
        $group = [
            self::STATIC_ARTICLE_GROUP_INTRO_DB => self::STATIC_ARTICLE_GROUP_INTRO_LABEL,
            self::STATIC_ARTICLE_GROUP_GUIDE_DB => self::STATIC_ARTICLE_GROUP_GUIDE_LABEL,
            self::STATIC_ARTICLE_GROUP_COLLABORATOR_DB => self::STATIC_ARTICLE_GROUP_COLLABORATOR_LABEL,
            self::STATIC_ARTICLE_GROUP_FAQ_DB => self::STATIC_ARTICLE_GROUP_FAQ_LABEL,
            self::STATIC_ARTICLE_GROUP_TEACHER_DB => self::STATIC_ARTICLE_GROUP_TEACHER_LABEL,
        ];

        if($value !== null && isset($group[$value]))
            return $group[$value];

        return $group;
    }
}