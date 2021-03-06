@extends('backend.layouts.main')

@section('page_heading', 'Chỉnh Sửa Cộng Tác Viên - ' . $collaborator->username)

@section('section')

    <form action="{{ action('Backend\CollaboratorController@editCollaborator', ['id' => $collaborator->id]) }}" method="post">

        <div class="box box-primary">
            <div class="box-header with-border">
                <button type="submit" class="btn btn-primary">Cập Nhật</button>
                <a href="{{ \App\Libraries\Helpers\Utility::getBackUrlCookie(action('Backend\UserController@adminUserCollaborator')) }}" class="btn btn-default">Quay Lại</a>
                <a href="{{ action('Backend\CollaboratorController@adminCollaboratorTransaction', ['id' => $collaborator->id]) }}" class="btn btn-primary">Lịch Sử Hoa Hồng</a>
            </div>
            <div class="box-body">
                <div class="row">
                    <div class="col-sm-4">
                        <div class="form-group">
                            <label>Mã</label>
                            <span class="form-control no-border">{{ $collaborator->collaboratorInformation->code }}</span>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-sm-4">
                        <div class="form-group{{ $errors->has('parent_user_name') ? ' has-error': '' }}">
                            <label>Quản Lý</label>
                            <input type="text" class="form-control" id="ParentInput" name="parent_user_name" placeholder="name - email" value="{{ old('parent_user_name', (!empty($collaborator->collaboratorInformation->parentCollaborator) ? ($collaborator->collaboratorInformation->parentCollaborator->user->profile->name . '-' . $collaborator->collaboratorInformation->parentCollaborator->user->email) : '')) }}" />
                            @if($errors->has('parent_user_name'))
                                <span class="help-block">{{ $errors->first('parent_user_name') }}</span>
                            @endif
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-sm-4">
                        <div class="form-group">
                            <label>Trạng Thái</label>
                            <?php
                            $status = old('status', $collaborator->collaboratorInformation->status);
                            ?>
                            <div class="checkbox">
                                <label>
                                    <input type="checkbox" name="status" value="{{ \App\Models\Collaborator::STATUS_ACTIVE_DB }}"<?php echo ($status == \App\Models\Collaborator::STATUS_ACTIVE_DB ? ' checked="checked"' : ''); ?> data-toggle="toggle" data-on="{{ \App\Models\Collaborator::STATUS_ACTIVE_LABEL }}" data-off="{{ \App\Models\Collaborator::STATUS_INACTIVE_LABEL }}" data-onstyle="success" data-offstyle="danger" />
                                </label>
                            </div>
                        </div>
                    </div>
                    <div class="col-sm-12">
                        <div class="form-group">
                            <label>Cấp Bậc</label>
                            <?php
                            $rankId = old('rank_id', $collaborator->collaboratorInformation->rank_id);
                            ?>
                            <div>
                                @foreach(\App\Models\Collaborator::getCollaboratorRank() as $value => $label)
                                    @if($rankId == $value)
                                        <label class="radio-inline">
                                            <input type="radio" name="rank_id" checked="checked" value="{{ $value }}">{{ $label }}
                                        </label>
                                    @else
                                        <label class="radio-inline">
                                            <input type="radio" name="rank_id" value="{{ $value }}">{{ $label }}
                                        </label>
                                    @endif
                                @endforeach
                            </div>
                        </div>
                    </div>
                    <div class="col-sm-4">
                        <div class="form-group{{ $errors->has('create_discount_percent') ? ' has-error': '' }}">
                            <label>Mức Giảm Giá Được Tạo <i>(bắt buộc)</i></label>
                            <div class="input-group">
                                <input type="text" class="form-control" id="CreateDiscountPercentInput" name="create_discount_percent" value="{{ old('create_discount_percent', $collaborator->collaboratorInformation->create_discount_percent) }}" required="required" />
                                <span class="input-group-addon">%</span>
                            </div>
                            @if($errors->has('create_discount_percent'))
                                <span class="help-block">{{ $errors->first('create_discount_percent') }}</span>
                            @endif
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-sm-4">
                        <div class="form-group{{ $errors->has('commission_percent') ? ' has-error': '' }}">
                            <label>Mức Hoa Hồng Được Hưởng <i>(bắt buộc)</i></label>
                            <div class="input-group">
                                <input type="text" class="form-control" id="CommissionPercentInput" name="commission_percent" value="{{ old('commission_percent', $collaborator->collaboratorInformation->commission_percent) }}" required="required" />
                                <span class="input-group-addon">%</span>
                            </div>
                            @if($errors->has('commission_percent'))
                                <span class="help-block">{{ $errors->first('commission_percent') }}</span>
                            @endif
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-sm-4">
                        <div class="form-group">
                            <label>Doanh Thu Hiện Tại</label>
                            <span class="form-control no-border">{{ \App\Libraries\Helpers\Utility::formatNumber($collaborator->collaboratorInformation->current_revenue) . ' VND' }}</span>
                        </div>
                    </div>
                    <div class="col-sm-4">
                        <div class="form-group">
                            <label>Doanh Thu Tổng</label>
                            <span class="form-control no-border">{{ \App\Libraries\Helpers\Utility::formatNumber($collaborator->collaboratorInformation->total_revenue) . ' VND' }}</span>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-sm-4">
                        <div class="form-group">
                            <label>Hoa Hồng Hiện Tại</label>
                            <span class="form-control no-border">{{ \App\Libraries\Helpers\Utility::formatNumber($collaborator->collaboratorInformation->current_commission) . ' VND' }}</span>
                        </div>
                    </div>
                    <div class="col-sm-4">
                        <div class="form-group">
                            <label>Hoa Hồng Tổng</label>
                            <span class="form-control no-border">{{ \App\Libraries\Helpers\Utility::formatNumber($collaborator->collaboratorInformation->total_commission) . ' VND' }}</span>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-sm-4">
                        <div class="form-group">
                            <label>Ngân Hàng</label>
                            <input type="text" class="form-control" name="bank" value="{{ old('bank', $collaborator->collaboratorInformation->bank) }}" />
                        </div>
                    </div>
                    <div class="col-sm-4">
                        <div class="form-group">
                            <label>Chủ Tài Khoản</label>
                            <input type="text" class="form-control" name="bank_holder" value="{{ old('bank_holder', $collaborator->collaboratorInformation->bank_holder) }}" />
                        </div>
                    </div>
                    <div class="col-sm-4">
                        <div class="form-group">
                            <label>Số Tài Khoản</label>
                            <input type="text" class="form-control" name="bank_number" value="{{ old('bank_number', $collaborator->collaboratorInformation->bank_number) }}" />
                        </div>
                    </div>
                </div>
            </div>
            <div class="box-footer">
                <button type="submit" class="btn btn-primary">Cập Nhật</button>
                <a href="{{ \App\Libraries\Helpers\Utility::getBackUrlCookie(action('Backend\UserController@adminUserCollaborator')) }}" class="btn btn-default">Quay Lại</a>
                <a href="{{ action('Backend\CollaboratorController@adminCollaboratorTransaction', ['id' => $collaborator->id]) }}" class="btn btn-primary">Lịch Sử Hoa Hồng</a>
            </div>
        </div>
        {{ csrf_field() }}

    </form>

@stop

@push('stylesheets')
    <link rel="stylesheet" href="{{ asset('assets/css/bootstrap-toggle.min.css') }}">
@endpush

@push('scripts')
    <script src="{{ asset('assets/js/bootstrap-toggle.min.js') }}"></script>
    <script type="text/javascript">
        $('#ParentInput').autocomplete({
            minLength: 3,
            delay: 1000,
            source: function(request, response) {
                $.ajax({
                    url: '{{ action('Backend\UserController@autoCompleteUser') }}',
                    type: 'post',
                    data: '_token=' + $('input[name="_token"]').first().val() + '&except={{ $collaborator->id }}&term=' + request.term,
                    success: function(result) {
                        if(result)
                        {
                            result = JSON.parse(result);
                            response(result);
                        }
                    }
                });
            },
            select: function(event, ui) {
                $(this).val(ui.item.name + ' - ' + ui.item.email);
                return false;
            }
        }).autocomplete('instance')._renderItem = function(ul, item) {
            return $('<li>').append('<a>' + item.name + ' - ' + item.email + '</a>').appendTo(ul);
        };

        $('input[type="radio"][name="rank_id"]').change(function() {
            $.ajax({
                url: '{{ action('Backend\SettingController@getSettingCollaboratorValue') }}',
                type: 'post',
                data: '_token=' + $('input[name="_token"]').first().val() + '&id=' + $('input[type="radio"][name="rank_id"]:checked').val(),
                success: function(result) {
                    if(result)
                    {
                        result = JSON.parse(result);

                        $('#CreateDiscountPercentInput').val(result['{{ \App\Models\Collaborator::DISCOUNT_ATTRIBUTE }}']);
                        $('#CommissionPercentInput').val(result['{{ \App\Models\Collaborator::COMMISSION_ATTRIBUTE }}']);
                    }
                }
            });
        });
    </script>
@endpush