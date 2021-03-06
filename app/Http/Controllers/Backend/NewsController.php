<?php

namespace App\Http\Controllers\Backend;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Libraries\Widgets\GridView;
use App\Libraries\Helpers\Html;
use App\Libraries\Helpers\Utility;
use App\Models\NewsCategory;
use App\Models\NewsArticle;
use App\Models\Course;

class NewsController extends Controller
{
    public function adminCategory(Request $request)
    {
        $dataProvider = NewsCategory::select('id', 'name', 'status', 'order')->orderBy('id', 'desc');

        $inputs = $request->all();

        if(count($inputs) > 0)
        {
            if(!empty($inputs['name']))
                $dataProvider->where('name', 'like', '%' . $inputs['name'] . '%');

            if(isset($inputs['status']) && $inputs['status'] !== '')
                $dataProvider->where('status', $inputs['status']);
        }

        $dataProvider = $dataProvider->paginate(GridView::ROWS_PER_PAGE);

        $columns = [
            [
                'title' => 'Tên',
                'data' => function($row) {
                    echo Html::a($row->name, [
                        'href' => action('Backend\NewsController@editCategory', ['id' => $row->id]),
                    ]);
                },
            ],
            [
                'title' => 'Thứ Tự',
                'data' => 'order',
            ],
            [
                'title' => 'Trạng Thái',
                'data' => function($row) {
                    $status = Utility::getTrueFalse($row->status);
                    if($row->status == Utility::ACTIVE_DB)
                        echo Html::span($status, ['class' => 'label label-success']);
                    else
                        echo Html::span($status, ['class' => 'label label-danger']);
                },
            ],
        ];

        $gridView = new GridView($dataProvider, $columns);
        $gridView->setCheckbox();
        $gridView->setFilters([
            [
                'title' => 'Tên',
                'name' => 'name',
                'type' => 'input',
            ],
            [
                'title' => 'Trạng Thái',
                'name' => 'status',
                'type' => 'select',
                'options' => Utility::getTrueFalse(),
            ],
        ]);
        $gridView->setFilterValues($inputs);

        return view('backend.news.admin_category', [
            'gridView' => $gridView,
        ]);
    }

    public function createCategory(Request $request)
    {
        Utility::setBackUrlCookie($request, '/admin/newsCategory?');

        $category = new NewsCategory();
        $category->status = Utility::ACTIVE_DB;
        $category->order = 1;

        return $this->saveCategory($request, $category);
    }

    public function editCategory(Request $request, $id)
    {
        Utility::setBackUrlCookie($request, '/admin/newsCategory?');

        $category = NewsCategory::find($id);

        if(empty($category))
            return view('backend.errors.404');

        return $this->saveCategory($request, $category, false);
    }

    protected function saveCategory($request, $category, $create = true)
    {
        if($request->isMethod('post'))
        {
            $inputs = $request->all();

            $validator = Validator::make($inputs, [
                'name' => 'required|unique:news_category,name' . ($create == true ? '' : (',' . $category->id)),
                'name_en' => 'nullable|unique:news_category,name_en' . ($create == true ? '' : (',' . $category->id)),
                'order' => 'required|integer|min:1',
                'slug' => 'nullable|unique:news_category,slug' . ($create == true ? '' : (',' . $category->id)),
                'slug_en' => 'nullable|unique:news_category,slug_en' . ($create == true ? '' : (',' . $category->id)),
            ]);

            if($validator->passes())
            {
                try
                {
                    DB::beginTransaction();

                    $category->name = $inputs['name'];
                    $category->name_en = $inputs['name_en'];
                    $category->status = isset($inputs['status']) ? Utility::ACTIVE_DB : Utility::INACTIVE_DB;
                    $category->order = $inputs['order'];
                    $category->image = $inputs['image'];

                    if(empty($inputs['slug']))
                        $category->slug = str_slug($category->name);
                    else
                        $category->slug = str_slug($inputs['slug']);

                    if(empty($inputs['slug_en']))
                        $category->slug_en = str_slug($category->name_en);
                    else
                        $category->slug_en = str_slug($inputs['slug_en']);

                    $details = array();
                    if(isset($inputs['detail']))
                    {
                        foreach($inputs['detail'] as $attribute => $attributeItems)
                        {
                            foreach($attributeItems as $key => $item)
                            {
                                if(!empty($item))
                                    $details[$key][$attribute] = $item;
                            }
                        }
                    }
                    $category->rss = json_encode($details);

                    if($create == true)
                        $category->created_at = date('Y-m-d H:i:s');

                    $category->save();

                    DB::commit();

                    return redirect()->action('Backend\NewsController@editCategory', ['id' => $category->id])->with('messageSuccess', 'Thành Công');
                }
                catch(\Exception $e)
                {
                    DB::rollBack();

                    if($create == true)
                        return redirect()->action('Backend\NewsController@createCategory')->withInput()->with('messageError', $e->getMessage());
                    else
                        return redirect()->action('Backend\NewsController@editCategory', ['id' => $category->id])->withInput()->with('messageError', $e->getMessage());
                }
            }
            else
            {
                if($create == true)
                    return redirect()->action('Backend\NewsController@createCategory')->withErrors($validator)->withInput();
                else
                    return redirect()->action('Backend\NewsController@editCategory', ['id' => $category->id])->withErrors($validator)->withInput();
            }
        }

        if($create == true)
        {
            return view('backend.news.create_category', [
                'category' => $category,
            ]);
        }
        else
        {
            return view('backend.news.edit_category', [
                'category' => $category,
            ]);
        }
    }

    public function deleteCategory($id)
    {
        $category = NewsCategory::find($id);

        if(empty($case))
            return view('backend.errors.404');

        $category->delete();

        return redirect(Utility::getBackUrlCookie(action('Backend\NewsController@adminCategory')))->with('messageSuccess', 'Thành Công');
    }

    public function controlDeleteCategory(Request $request)
    {
        $ids = $request->input('ids');

        $categories = NewsCategory::whereIn('id', explode(';', $ids))->get();

        foreach($categories as $category)
            $category->delete();

        if($request->headers->has('referer'))
            return redirect($request->headers->get('referer'))->with('messageSuccess', 'Thành Công');
        else
            return redirect()->action('Backend\NewsController@adminCategory')->with('messageSuccess', 'Thành Công');
    }

    public function adminArticle(Request $request)
    {
        $dataProvider = NewsArticle::with(['newsCategory' => function($query) {
            $query->select('id', 'name');
        }])->select('id', 'name', 'status', 'view_count', 'category_id')
            ->orderBy('id', 'desc');

        $inputs = $request->all();

        if(count($inputs) > 0)
        {
            if(!empty($inputs['name']))
                $dataProvider->where('name', 'like', '%' . $inputs['name'] . '%');

            if(!empty($inputs['category_id']))
            {
                $dataProvider->where('category_id', $inputs['category_id']);
            }

            if(isset($inputs['status']) && $inputs['status'] !== '')
                $dataProvider->where('status', $inputs['status']);
        }

        $dataProvider = $dataProvider->paginate(GridView::ROWS_PER_PAGE);

        $columns = [
            [
                'title' => 'Tên',
                'data' => function($row) {
                    echo Html::a($row->name, [
                        'href' => action('Backend\NewsController@editArticle', ['id' => $row->id]),
                    ]);
                },
            ],
            [
                'title' => 'Chuyên Mục',
                'data' => function($row) {
                    echo $row->newsCategory->name;
                },
            ],
            [
                'title' => 'Trạng Thái',
                'data' => function($row) {
                    $status = Course::getCourseStatus($row->status);
                    if($row->status == Course::STATUS_PUBLISH_DB)
                        echo Html::span($status, ['class' => 'label label-success']);
                    else if($row->status == Course::STATUS_FINISH_DB)
                        echo Html::span($status, ['class' => 'label label-primary']);
                    else
                        echo Html::span($status, ['class' => 'label label-danger']);
                },
            ],
            [
                'title' => 'Lượt Xem',
                'data' => function($row) {
                    echo Utility::formatNumber($row->view_count);
                },
            ],
        ];

        $gridView = new GridView($dataProvider, $columns);
        $gridView->setCheckbox();
        $gridView->setFilters([
            [
                'title' => 'Tên',
                'name' => 'name',
                'type' => 'input',
            ],
            [
                'title' => 'Chuyên Mục',
                'name' => 'category_id',
                'type' => 'select',
                'options' => NewsCategory::pluck('name', 'id'),
            ],
            [
                'title' => 'Trạng Thái',
                'name' => 'status',
                'type' => 'select',
                'options' => Course::getCourseStatus(),
            ],
        ]);
        $gridView->setFilterValues($inputs);

        return view('backend.news.admin_article', [
            'gridView' => $gridView,
        ]);
    }

    public function createArticle(Request $request)
    {
        Utility::setBackUrlCookie($request, '/admin/newsArticle?');

        $article = new NewsArticle();
        $article->status = Course::STATUS_DRAFT_DB;
        $article->category_status = Utility::ACTIVE_DB;
        $article->order = 1;

        return $this->saveArticle($request, $article);
    }

    public function editArticle(Request $request, $id)
    {
        Utility::setBackUrlCookie($request, '/admin/newsArticle?');

        $article = NewsArticle::find($id);

        if(empty($article))
            return view('backend.errors.404');

        return $this->saveArticle($request, $article, false);
    }

    protected function saveArticle($request, $article, $create = true)
    {
        if($request->isMethod('post'))
        {
            $inputs = $request->all();

            $validator = Validator::make($inputs, [
                'name' => 'required|unique:news_article,name' . ($create == true ? '' : (',' . $article->id)),
                'name_en' => 'nullable|unique:news_article,name_en' . ($create == true ? '' : (',' . $article->id)),
                'content' => 'required',
                'slug' => 'nullable|unique:news_article,slug' . ($create == true ? '' : (',' . $article->id)),
                'slug_en' => 'nullable|unique:news_article,slug_en' . ($create == true ? '' : (',' . $article->id)),
                'category_id' => 'required',
                'order' => 'required|integer|min:1',
            ]);

            if($validator->passes())
            {
                $article->image = $inputs['image'];
                $article->name = $inputs['name'];
                $article->name_en = $inputs['name_en'];
                $article->status = $inputs['status'];
                $article->content = $inputs['content'];
                $article->content_en = $inputs['content_en'];
                $article->short_description = $inputs['short_description'];
                $article->short_description_en = $inputs['short_description_en'];
                $article->order = $inputs['order'];
                $article->category_id = $inputs['category_id'];

                if(empty($article->published_at) && $article->status == Course::STATUS_PUBLISH_DB)
                    $article->published_at = date('Y-m-d H:i:s');

                if(empty($inputs['slug']))
                    $article->slug = str_slug($article->name);
                else
                    $article->slug = str_slug($inputs['slug']);

                if(empty($inputs['slug_en']))
                    $article->slug_en = str_slug($article->name_en);
                else
                    $article->slug_en = str_slug($inputs['slug_en']);

                if($create == true)
                    $article->created_at = date('Y-m-d H:i:s');

                $category = NewsCategory::find($inputs['category_id']);
                if(empty($category))
                    $article->category_status = Utility::INACTIVE_DB;
                else
                    $article->category_status = $category->status;

                $article->save();

                return redirect()->action('Backend\NewsController@editArticle', ['id' => $article->id])->with('messageSuccess', 'Thành Công');
            }
            else
            {
                if($create == true)
                    return redirect()->action('Backend\NewsController@createArticle')->withErrors($validator)->withInput();
                else
                    return redirect()->action('Backend\NewsController@editArticle', ['id' => $article->id])->withErrors($validator)->withInput();
            }
        }

        $categories = NewsCategory::pluck('name', 'id');

        if($create == true)
        {
            return view('backend.news.create_article', [
                'article' => $article,
                'categories' => $categories,
            ]);
        }
        else
        {
            return view('backend.news.edit_article', [
                'article' => $article,
                'categories' => $categories,
            ]);
        }
    }

    public function deleteArtcle($id)
    {

    }

    public function controlDeleteArticle(Request $request)
    {

    }
}