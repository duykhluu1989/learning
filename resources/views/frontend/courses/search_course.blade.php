@extends('frontend.layouts.main')

@section('page_heading', trans('theme.search_title'))

@section('section')

    @include('frontend.layouts.partials.header')

    @include('frontend.layouts.partials.headtext')

    @include('frontend.layouts.partials.breabcrumb', ['breabcrumbTitle' => (trans('theme.search_title') . ': ' . $keyword)])

    <main>
        <section class="khoahoc bg_gray">
            <div class="container">
                @if(!empty($courses) && $courses->total() > 0)
                    <div class="row">
                        @foreach($courses as $course)
                            <div class="col-lg-3 col-md-3 col-sm-3 col-xs-12 item_khoahoc">
                                <a href="{{ action('Frontend\CourseController@detailCourse', ['id' => $course->id, 'slug' => \App\Libraries\Helpers\Utility::getValueByLocale($course, 'slug')]) }}"><img src="{{ $course->image }}" alt="{{ \App\Libraries\Helpers\Utility::getValueByLocale($course, 'name') }}" class="img-responsive w100p"></a>
                                <h4><a href="{{ action('Frontend\CourseController@detailCourse', ['id' => $course->id, 'slug' => \App\Libraries\Helpers\Utility::getValueByLocale($course, 'slug')]) }}">{{ \App\Libraries\Helpers\Utility::getValueByLocale($course, 'name') }}</a></h4>
                                <p class="name_gv">{{ $course->user->profile->name }}</p>
                                <p class="price">
                                    @if($course->validatePromotionPrice())
                                        {{ \App\Libraries\Helpers\Utility::formatNumber($course->promotionPrice->price) . 'đ' }}
                                    @else
                                        {{ \App\Libraries\Helpers\Utility::formatNumber($course->price) . 'đ' }}
                                    @endif
                                </p>
                                <div class="ticker2">
                                    <p><span class="view"><i class="fa fa-eye" aria-hidden="true"></i> {{ \App\Libraries\Helpers\Utility::formatNumber($course->view_count) }}</span> - <span class="buy"><i class="fa fa-money" aria-hidden="true"></i> {{ \App\Libraries\Helpers\Utility::formatNumber($course->bought_count) }}</span></p>
                                </div>
                                <a href="javascript:void(0)" class="btn btnYellow btn-block PreviewCourse" data-url="{{ action('Frontend\CourseController@previewCourse', ['id' => $course->id, 'slug' => \App\Libraries\Helpers\Utility::getValueByLocale($course, 'slug')]) }}">@lang('theme.preview')</a>
                                <a href="{{ action('Frontend\CourseController@detailCourse', ['id' => $course->id, 'slug' => \App\Libraries\Helpers\Utility::getValueByLocale($course, 'slug')]) }}" class="btn btnRed btn-block">@lang('theme.view_detail')</a>
                            </div>
                        @endforeach
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
                @else
                    <div class="row">
                        <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12 item_khoahoc">
                            @lang('theme.search_no_result')
                        </div>
                    </div>
                @endif
            </div>
        </section>
    </main>

    <div id="modal_xemKH" class="modal fade bs-example-modal-lg">
        <div class="modal-dialog modal-lg">
            <div class="modal-content" id="ModalXemHKContent">

                @include('frontend.courses.partials.preview_course', ['course' => null])

            </div>
        </div>
    </div>

@stop

@include('frontend.courses.partials.add_cart_item')

@push('scripts')
    <script type="text/javascript">
        $('.PreviewCourse').click(function() {
            if($(this).data('url') != '')
            {
                $.ajax({
                    url: $(this).data('url'),
                    type: 'get',
                    success: function(result) {
                        if(result)
                        {
                            $('#ModalXemHKContent').html(result);

                            try
                            {
                                FB.XFBML.parse();
                            }
                            catch(exception)
                            {

                            }

                            $('#modal_xemKH').modal('show');
                        }
                    }
                });
            }
        });
    </script>
@endpush