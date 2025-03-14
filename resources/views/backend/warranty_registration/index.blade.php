@extends('backend.layouts.app')

@section('content')

@php
    CoreComponentRepository::instantiateShopRepository();
    CoreComponentRepository::initializeCache();
@endphp

<div class="aiz-titlebar text-left mt-2 mb-3">
    <div class="row align-items-center">
        <div class="col-auto">
            <h1 class="h3">{{ translate('All Warranty Registrations') }}</h1>
        </div>
    </div>
</div>
<br>

<div class="card">
    <form id="sort_warranties" action="" method="GET">
        <div class="card-header row gutters-5">
            <div class="col">
                <h5 class="mb-md-0 h6">{{ translate('All Warranty Registrations') }}</h5>
            </div>

            @can('warranty_delete')
                <div class="dropdown mb-2 mb-md-0">
                    <button class="btn border dropdown-toggle" type="button" data-toggle="dropdown">
                        {{ translate('Bulk Action') }}
                    </button>
                    <div class="dropdown-menu dropdown-menu-right">
                        <a class="dropdown-item confirm-alert" href="javascript:void(0)" data-target="#bulk-delete-modal">{{ translate('Delete selection') }}</a>
                    </div>
                </div>
            @endcan

            <div class="col-md-2">
                <select class="form-control form-control-sm aiz-selectpicker mb-2 mb-md-0" name="type" id="type" onchange="sort_warranties()">
                    <option value="">{{ translate('Sort By') }}</option>
                    <option value="created_at,desc" @if(request('type') == 'created_at,desc') selected @endif>{{ translate('Newest First') }}</option>
                    <option value="created_at,asc" @if(request('type') == 'created_at,asc') selected @endif>{{ translate('Oldest First') }}</option>
                </select>
            </div>

            <div class="col-md-2">
                <div class="form-group mb-0">
                    <input type="text" class="form-control form-control-sm" id="search" name="search" value="{{ request('search') }}" placeholder="{{ translate('Type & Enter') }}">
                </div>
            </div>
        </div>

        <div class="card-body">
            <table class="table aiz-table mb-0">
                <thead class="text-gray fs-12">
                    <tr>
                        <th>{{ translate('Product Name') }}</th>
                        <th>{{ translate('Serial No') }}</th>
                        <th>{{ translate('Date Of Purchase') }}</th>
                        <th>{{ translate('Bill Image') }}</th>
                        <th>{{ translate('Status') }}</th>
                        <th class="text-right">{{ translate('Options') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($warranties as $registration)
                        <tr>
                            <td>{{ $registration->product->name ?? 'N/A' }}</td>
                            <td>{{ $registration->serial_no }}</td>
                            <td>{{ date('d-m-Y', strtotime($registration->date_of_purchase)) }}</td>
                            <td>
                                @if($registration->bill_image)
                                    <a href="{{ asset($registration->bill_image) }}" target="_blank">
                                        <img src="{{ asset($registration->bill_image) }}" alt="Bill Image" width="50" height="50" class="img-thumbnail">
                                    </a>
                                @else
                                    {{ translate('No Image') }}
                                @endif
                            </td>
                            <td>{{ $registration->status ? translate('Approved') : translate('Pending') }}</td>
                            <td class="text-right">
                                <a href="{{ route('warranty_registration.details', $registration->id) }}" class="btn btn-soft-info btn-sm">{{ translate('View') }}</a>
                                @can('warranty_delete')
                                    <a href="javascript:void(0)" class="btn btn-soft-danger btn-sm confirm-delete" data-href="{{ route('warranty_registration_admin.destroy', $registration->id) }}">{{ translate('Cancel') }}</a>
                                @endcan
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>

            <div class="aiz-pagination">
                {{ $warranties->appends(request()->input())->links() }}
            </div>
        </div>
    </form>
</div>

@endsection

@section('modal')
    <!-- Delete modal -->
    @include('modals.delete_modal')
    <!-- Bulk Delete modal -->
    @include('modals.bulk_delete_modal')
@endsection


@section('script')
    <script type="text/javascript">

        $(document).on("change", ".check-all", function() {
            if(this.checked) {
                // Iterate each checkbox
                $('.check-one:checkbox').each(function() {
                    this.checked = true;
                });
            } else {
                $('.check-one:checkbox').each(function() {
                    this.checked = false;
                });
            }

        });

        // function update_published(el){

        //     if('{{env('DEMO_MODE')}}' == 'On'){
        //         AIZ.plugins.notify('info', '{{ translate('Data can not change in demo mode.') }}');
        //         return;
        //     }

        //     if(el.checked){
        //         var status = 1;
        //     }
        //     else{
        //         var status = 0;
        //     }
        //     $.post('{{ route('products.published') }}', {_token:'{{ csrf_token() }}', id:el.value, status:status}, function(data){
        //         if(data == 1){
        //             AIZ.plugins.notify('success', '{{ translate('Published products updated successfully') }}');
        //         }
        //         else{
        //             AIZ.plugins.notify('danger', '{{ translate('Something went wrong') }}');
        //         }
        //     });
        // }

        function update_approved(el){

            if('{{env('DEMO_MODE')}}' == 'On'){
                AIZ.plugins.notify('info', '{{ translate('Data can not change in demo mode.') }}');
                return;
            }

            if(el.checked){
                var approved = 1;
            }
            else{
                var approved = 0;
            }
            $.post('{{ route('products.approved') }}', {
                _token      :   '{{ csrf_token() }}',
                id          :   el.value,
                approved    :   approved
            }, function(data){
                if(data == 1){
                    AIZ.plugins.notify('success', '{{ translate('Product approval update successfully') }}');
                }
                else{
                    AIZ.plugins.notify('danger', '{{ translate('Something went wrong') }}');
                }
            });
        }

        // function update_featured(el){
        //     if('{{env('DEMO_MODE')}}' == 'On'){
        //         AIZ.plugins.notify('info', '{{ translate('Data can not change in demo mode.') }}');
        //         return;
        //     }

        //     if(el.checked){
        //         var status = 1;
        //     }
        //     else{
        //         var status = 0;
        //     }
        //     $.post('{{ route('products.featured') }}', {_token:'{{ csrf_token() }}', id:el.value, status:status}, function(data){
        //         if(data == 1){
        //             AIZ.plugins.notify('success', '{{ translate('Featured products updated successfully') }}');
        //         }
        //         else{
        //             AIZ.plugins.notify('danger', '{{ translate('Something went wrong') }}');
        //         }
        //     });
        // }

        function sort_products(el){
            $('#sort_products').submit();
        }

        function bulk_delete() {
            var data = new FormData($('#sort_products')[0]);
            $.ajax({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                url: "{{route('bulk-product-delete')}}",
                type: 'POST',
                data: data,
                cache: false,
                contentType: false,
                processData: false,
                success: function (response) {
                    if(response == 1) {
                        location.reload();
                    }
                }
            });
        }

    </script>
@endsection
