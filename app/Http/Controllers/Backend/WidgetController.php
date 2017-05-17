<?php

namespace App\Http\Controllers\Backend;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Libraries\Widgets\GridView;
use App\Libraries\Helpers\Html;
use App\Models\Widget;

class WidgetController extends Controller
{
    public function adminWidget(Request $request)
    {
        $dataProvider = Widget::select('id', 'name', 'status', 'type');

        $inputs = $request->all();

        if(count($inputs) > 0)
        {
            if(!empty($inputs['name']))
                $dataProvider->where('name', 'like', '%' . $inputs['name'] . '%');

            if(isset($inputs['type']) && $inputs['type'] !== '')
                $dataProvider->where('type', $inputs['type']);

            if(isset($inputs['status']) && $inputs['status'] !== '')
                $dataProvider->where('status', $inputs['status']);
        }

        $dataProvider = $dataProvider->paginate(GridView::ROWS_PER_PAGE);

        $columns = [
            [
                'title' => 'Tên',
                'data' => function($row) {
                    echo Html::a($row->name, [
                        'href' => action('Backend\WidgetController@editWidget', ['id' => $row->id]),
                    ]);
                },
            ],
            [
                'title' => 'Loại',
                'data' => function($row) {
                    echo Widget::getWidgetType($row->type);
                },
            ],
            [
                'title' => 'Trạng Thái',
                'data' => function($row) {
                    $status = Widget::getWidgetStatus($row->status);
                    if($row->status == Widget::STATUS_ACTIVE_DB)
                        echo Html::span($status, ['class' => 'text-green']);
                    else
                        echo Html::span($status, ['class' => 'text-red']);
                },
            ],
        ];

        $gridView = new GridView($dataProvider, $columns);
        $gridView->setFilters([
            [
                'title' => 'Tên',
                'name' => 'name',
                'type' => 'input',
            ],
            [
                'title' => 'Loại',
                'name' => 'type',
                'type' => 'select',
                'options' => Widget::getWidgetType(),
            ],
            [
                'title' => 'Trạng Thái',
                'name' => 'status',
                'type' => 'select',
                'options' => Widget::getWidgetStatus(),
            ],
        ]);
        $gridView->setFilterValues($inputs);

        return view('backend.widgets.admin_widget', [
            'gridView' => $gridView,
        ]);
    }

    public function editWidget(Request $request, $id)
    {
        $widget = Widget::find($id);

        if(empty($widget))
            return view('backend.errors.404');

        return view('backend.widgets.edit_widget', [
            'widget' => $widget,
        ]);
    }
}