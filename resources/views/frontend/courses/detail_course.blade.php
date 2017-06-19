@extends('frontend.layouts.main')

@section('page_heading', 'Home')

@section('section')

    @include('frontend.layouts.partials.header')

    @include('frontend.layouts.partials.course_breadcrumb')

    <main>
        <section class="ct_khoahoc">
            <div class="container">
                <div class="row">
                    <div class="col-lg-12">
                        <h2>{{ \App\Libraries\Helpers\Utility::getValueByLocale($course, 'name') }}</h2>
                        <p>{{ \App\Libraries\Helpers\Utility::getValueByLocale($course, 'short_description') }}</p>
                        <div class="ticker2 w135 mb15">
                            <p><span class="view"><i class="fa fa-eye" aria-hidden="true"></i> {{ \App\Libraries\Helpers\Utility::formatNumber($course->view_count) }}</span> - <span class="buy"><i class="fa fa-money" aria-hidden="true"></i> {{ \App\Libraries\Helpers\Utility::formatNumber($course->bought_count) }}</span></p>
                        </div>
                        <p>@lang('theme.teacher') <span class="red"><b>{{ $course->user->profile->name }}</b></span></p>
                    </div>
                </div>
                <div class="row">
                    <div class="col-lg-6 boxmH display_table">
                        <div class="table_content">
                            <a href="javascript:void(0)"><img src="{{ $course->image }}" alt="{{ \App\Libraries\Helpers\Utility::getValueByLocale($course, 'name') }}" class="img-responsive w100p"></a>
                        </div>
                    </div>
                    <div class="col-lg-6 boxmH">
                        <div class="box_info_khoahoc">

                            @if($bought == false)
                                <p class="big_price"><i class="fa fa-tags" aria-hidden="true"></i>
                                    @if($course->validatePromotionPrice())
                                        <span class="new_price">{{ \App\Libraries\Helpers\Utility::formatNumber($course->promotionPrice->price) . ' VND' }}</span> - <span class="sale">({{ \App\Libraries\Helpers\Utility::formatNumber($course->price) . ' VND' }})</span> <span class="sale_percent">-{{ round(($course->price - $course->promotionPrice->price) * 100 / $course->price) }}%</span>
                                    @else
                                        <span class="new_price">{{ \App\Libraries\Helpers\Utility::formatNumber($course->price) . ' VND' }}</span>
                                    @endif
                                </p>
                            @endif

                            <div class="row">
                                <div class="col-lg-8">

                                    @if($bought == false)
                                        <a href="#" class="btn btn-lg btnMuaKH"><i class="fa fa-cart-plus" aria-hidden="true"></i>@lang('theme.buy_course')</a>
                                        <a href="#" class="btn btn-lg btnThemGH"><i class="fa fa-plus-square-o" aria-hidden="true"></i>@lang('theme.add_cart')</a>
                                        <p><a class="btn btn-link btn_nhapmaKM" href="#" data-toggle="modal" data-target="#modal_NhapMKM">@lang('theme.input_discount_code')</a></p>

                                        <div id="modal_NhapMKM" class="modal fade" tabindex="-1" role="dialog">
                                            <div class="modal-dialog modal-sm">
                                                <div class="modal-content">
                                                    <div class="modal-header">
                                                        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                                                        <h4 class="modal-title">@lang('theme.input_discount_code')</h4>
                                                    </div>
                                                    <div class="modal-body">
                                                        <form action="" method="post" class="form-inline" role="form">
                                                            <div class="form-group">
                                                                <label class="sr-only" for="">label</label>
                                                                <input type="email" class="form-control" name="discount_code" placeholder="@lang('theme.input_discount_code')">
                                                            </div>
                                                            <button type="submit" class="btn btnRed">@lang('theme.input')</button>
                                                        </form>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    @endif

                                    <div class="box_sl_baigiang">
                                        <p>@lang('theme.course_item_count'): <span><b>{{ $course->item_count }}</b></span></p>
                                        @if(!empty($course->video_length))
                                            <p>@lang('theme.video_length'): <span><b>{{ \App\Libraries\Helpers\Utility::formatTimeString($course->video_length) }}</b></span></p>
                                        @endif
                                    </div>
                                    <a class="btn btn_face" href="#"><i class="fa fa-facebook-square" aria-hidden="true"></i> Chia sẽ</a>
                                    <a class="btn btn_face" href="#"><i class="fa fa-thumbs-o-up" aria-hidden="true"></i> Thích</a>
                                </div>
                                <div class="col-lg-4">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="row mt30">
                    <div class="col-lg-12">
                        <ul class="nav nav-tabs tabs_info">
                            <li class="active"><a data-toggle="tab" href="#section_gioithieu">@lang('theme.course_description')</a></li>
                            <li><a data-toggle="tab" href="#section_chitiet">Chi tiết</a></li>
                            <li><a data-toggle="tab" href="#section_binhluan">Đánh giá & bình luận</a></li>
                        </ul>
                        <div class="tab-content tabs_info_content">
                            <div id="section_gioithieu" class="tab-pane fade in active">
                                <h4>@lang('theme.course_description_title')</h4>
                                <?php echo \App\Libraries\Helpers\Utility::getValueByLocale($course, 'description'); ?>
                            </div>
                            <div id="section_chitiet" class="tab-pane fade">
                                <h3>Section B</h3>
                                <p>Vestibulum nec erat eu nulla rhoncus fringilla ut non neque. Vivamus nibh urna, ornare id gravida ut, mollis a magna. Aliquam porttitor condimentum nisi, eu viverra ipsum porta ut. Nam hendrerit bibendum turpis, sed molestie mi fermentum id. Aenean volutpat velit sem. Sed consequat ante in rutrum convallis. Nunc facilisis leo at faucibus adipiscing.</p>
                            </div>
                            <div id="section_binhluan" class="tab-pane fade">
                                <h3>Dropdown 1</h3>
                                <p>WInteger convallis, nulla in sollicitudin placerat, ligula enim auctor lectus, in mollis diam dolor at lorem. Sed bibendum nibh sit amet dictum feugiat. Vivamus arcu sem, cursus a feugiat ut, iaculis at erat. Donec vehicula at ligula vitae venenatis. Sed nunc nulla, vehicula non porttitor in, pharetra et dolor. Fusce nec velit velit. Pellentesque consectetur eros.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </main>

@stop