<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Course extends Model
{
    const STATUS_PUBLISH_DB = 2;
    const STATUS_FINISH_DB = 1;
    const STATUS_DRAFT_DB = 0;
    const STATUS_PUBLISH_LABEL = 'Xuất Bản';
    const STATUS_FINISH_LABEL = 'Hoàn Thành';
    const STATUS_DRAFT_LABEL = 'Nháp';

    protected $table = 'course';

    public $timestamps = false;

    public function user()
    {
        return $this->belongsTo('App\Models\User', 'user_id');
    }

    public function level()
    {
        return $this->belongsTo('App\Models\Level', 'level_id');
    }

    public function categoryCourses()
    {
        return $this->hasMany('App\Models\CategoryCourse', 'course_id');
    }

    public function courseItems()
    {
        return $this->hasMany('App\Models\CourseItem', 'course_id');
    }

    public function tagCourses()
    {
        return $this->hasMany('App\Models\TagCourse', 'course_id');
    }

    public static function getCourseStatus($value = null)
    {
        $status = [
            self::STATUS_DRAFT_DB => self::STATUS_DRAFT_LABEL,
            self::STATUS_FINISH_DB => self::STATUS_FINISH_LABEL,
            self::STATUS_PUBLISH_DB => self::STATUS_PUBLISH_LABEL,
        ];

        if($value !== null && isset($status[$value]))
            return $status[$value];

        return $status;
    }

    public function isDeletable()
    {
        return false;
    }

    public function doDelete()
    {
        $this->delete();

        foreach($this->categoryCourses as $categoryCourse)
            $categoryCourse->delete();

        foreach($this->courseItems as $courseItem)
            $courseItem->delete();

        foreach($this->tagCourses as $tagCourse)
            $tagCourse->delete();
    }
}