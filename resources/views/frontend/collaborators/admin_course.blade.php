@extends('frontend.layouts.main')

@section('page_heading', trans('theme.collaborator'))

@section('section')

    @include('frontend.layouts.partials.header')

    @include('frontend.layouts.partials.headtext')

    @include('frontend.layouts.partials.breabcrumb', ['breabcrumbTitle' => trans('theme.collaborator')])

    <main>
        <section class="khoahoc bg_gray">
            <div class="container">
                <div class="row">
                    <div class="col-lg-3 col-md-3 col-sm-12 col-xs-12">
                        <div class="navleft">
                            <h5>@lang('theme.collaborator')</h5>

                            @include('frontend.collaborators.partials.navigation')

                        </div>
                    </div>
                    <div class="col-lg-9 col-md-9 col-sm-12 col-xs-12">
                        <div class="main_content">
                            <div class="row">
                                <h4>@lang('theme.coupon_code')</h4>
                                @if(empty($discount))
                                    <a href="{{ action('Frontend\CollaboratorController@generateDiscount') }}" class="btn btn-sm btnThemGH">@lang('theme.create_coupon')</a>
                                @else
                                    <form action="{{ action('Frontend\CollaboratorController@adminCourse') }}" method="post">
                                        <div class="col-sm-5">
                                            <label>{{ $discount->code }}</label>
                                        </div>
                                        <div class="col-sm-7">
                                            <div class="form-group">
                                                <label class="col-sm-6">@lang('theme.percent') <i>(@lang('theme.max'): {{ $user->collaboratorInformation->create_discount_percent . '%' }})</i></label>
                                                <div class="col-sm-6">
                                                    <div class="input-group">
                                                        <input type="text" class="form-control" name="percent" value="{{ old('percent', $discount->value) }}" required="required" />
                                                        <span class="input-group-addon">%</span>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        @if($errors->has('percent'))
                                            <div class="form-group has-error">
                                                <span class="help-block">* {{ $errors->first('percent') }}</span>
                                            </div>
                                        @endif
                                        <div class="text-center">
                                            <button type="submit" class="btn btn-lg btnRed"><i class="fa fa-floppy-o" aria-hidden="true"></i> @lang('theme.save')</button>
                                        </div>
                                        {{ csrf_field() }}
                                    </form>
                                @endif
                            </div>
                            <hr />
                            <div class="row table-responsive">
                                <h4>@lang('theme.all_course')</h4>
                                <table class="table table-hover table-bordered">
                                    <thead>
                                    <tr>
                                        <th></th>
                                        <th>@lang('theme.course')</th>
                                        <th>@lang('theme.category')</th>
                                        <th>@lang('theme.bought_count')</th>
                                        <th>@lang('theme.price')</th>
                                        <th></th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    <form action="{{ action('Frontend\CollaboratorController@adminCourse') }}" method="get">
                                        <tr>
                                            <td></td>
                                            <td>
                                                <input type="text" class="form-control" name="course" placeholder="@lang('theme.search_course')" value="{{ request()->input('course') }}" />
                                            </td>
                                            <td>
                                                <input type="text" class="form-control" name="category" placeholder="@lang('theme.search_category')" value="{{ request()->input('category') }}" />
                                            </td>
                                            <td></td>
                                            <td></td>
                                            <td>
                                                <button type="submit" class="hidden"></button>
                                            </td>
                                        </tr>
                                    </form>

                                    @foreach($courses as $course)
                                        <tr>
                                            <td class="col-sm-2">
                                                <a href="{{ action('Frontend\CourseController@detailCourse', ['id' => $course->id, 'slug' => \App\Libraries\Helpers\Utility::getValueByLocale($course, 'slug')]) }}">
                                                    <img src="{{ $course->image }}" width="100%" alt="{{ \App\Libraries\Helpers\Utility::getValueByLocale($course, 'name') }}" />
                                                </a>
                                            </td>
                                            <td>
                                                <a href="{{ action('Frontend\CourseController@detailCourse', ['id' => $course->id, 'slug' => \App\Libraries\Helpers\Utility::getValueByLocale($course, 'slug')]) }}">{{ \App\Libraries\Helpers\Utility::getValueByLocale($course, 'name') }}</a>
                                            </td>
                                            <td>{{ \App\Libraries\Helpers\Utility::getValueByLocale($course->category, 'name') }}</td>
                                            <td>{{ \App\Libraries\Helpers\Utility::formatNumber($course->bought_count) }}</td>
                                            <td>{{ \App\Libraries\Helpers\Utility::formatNumber($course->price) . 'đ' }}</td>
                                            <td class="text-center">
                                                <a href="{{ action('Frontend\CollaboratorController@getLinkCourse', ['id' => $course->id]) }}" class="btn btn-sm btnThemGH" target="_blank">@lang('theme.get_link')</a>
                                            </td>
                                        </tr>
                                    @endforeach

                                    </tbody>
                                </table>
                            </div>
                            <div class="row">
                                <div class="col-lg-12 text-center">
                                    <ul class="pagination">
                                        @if($courses->lastPage() > 1)
                                            @if($courses->currentPage() > 1)
                                                <li><a href="{{ $courses->previousPageUrl() }}">&laquo;</a></li>
                                                <li><a href="{{ $courses->url(1) }}">1</a></li>
                                            @endif

                                            @for($i = 2;$i >= 1;$i --)
                                                @if($courses->currentPage() - $i > 1)
                                                    @if($courses->currentPage() - $i > 2 && $i == 2)
                                                        <li>...</li>
                                                        <li><a href="{{ $courses->url($courses->currentPage() - $i) }}">{{ $courses->currentPage() - $i }}</a></li>
                                                    @else
                                                        <li><a href="{{ $courses->url($courses->currentPage() - $i) }}">{{ $courses->currentPage() - $i }}</a></li>
                                                    @endif
                                                @endif
                                            @endfor

                                            <li class="active"><a href="javascript:void(0)">{{ $courses->currentPage() }}</a></li>

                                            @for($i = 1;$i <= 2;$i ++)
                                                @if($courses->currentPage() + $i < $courses->lastPage())
                                                    @if($courses->currentPage() + $i < $courses->lastPage() - 1 && $i == 2)
                                                        <li><a href="{{ $courses->url($courses->currentPage() + $i) }}">{{ $courses->currentPage() + $i }}</a></li>
                                                        <li>...</li>
                                                    @else
                                                        <li><a href="{{ $courses->url($courses->currentPage() + $i) }}">{{ $courses->currentPage() + $i }}</a></li>
                                                    @endif
                                                @endif
                                            @endfor

                                            @if($courses->currentPage() < $courses->lastPage())
                                                <li><a href="{{ $courses->url($courses->lastPage()) }}">{{ $courses->lastPage() }}</a></li>
                                                <li><a href="{{ $courses->nextPageUrl() }}">&raquo;</a></li>
                                            @endif
                                        @endif
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </main>

@stop