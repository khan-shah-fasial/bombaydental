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

            <div class="col-md-2">
                <div class="form-group mb-0">
                    <label for="date_from" class="form-label">{{ translate('Search') }}</label>
                    <input type="text" class="form-control form-control-sm" id="search" name="search" value="{{ request('search') }}" placeholder="{{ translate('Type & Enter') }}">
                </div>
            </div>
            <!-- From Date -->
            <div class="col-md-2">
                <div class="form-group mb-0">
                    <label for="date_from" class="form-label">{{ translate('From Date') }}</label>
                    <input type="date" class="form-control form-control-sm" id="date_from" name="date_from" value="{{ request('date_from') }}">
                </div>
            </div>

            <!-- To Date -->
            <div class="col-md-2">
                <div class="form-group mb-0">
                    <label for="date_from" class="form-label">{{ translate('To Date') }}</label>
                    <input type="date" class="form-control form-control-sm" id="date_to" name="date_to" value="{{ request('date_to') }}">
                </div>
            </div>
            <div class="col-md-2">
                <div class="form-group mb-0 d-flex">
                    <button type="submit" class="btn btn-primary btn-sm me-2 mx-1">
                        <i class="las la-search"></i> {{ translate('Search') }}
                    </button>
                    <a href="{{ url()->current() }}" class="btn btn-secondary btn-sm">
                        <i class="las la-sync"></i> {{ translate('Reset') }}
                    </a>
                </div>
            </div>
        </div>
    </form>

    <div class="card-body">
        <table class="table aiz-table mb-0">
            <thead class="text-gray fs-12">
                <tr>
                    <th>{{ translate('User Name') }}</th>
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
                        <td>{{ $registration->user->name ?? 'N/A' }}</td>
                        <td>{{ $registration->product->name ?? 'N/A' }}</td>
                        <td>{{ $registration->serial_no }}</td>
                        <td>{{ date('d-m-Y', strtotime($registration->date_of_purchase)) }}</td>
                        <td>
                            @if($registration->bill_image)
                                @php
                                    $fileExtension = pathinfo($registration->bill_image, PATHINFO_EXTENSION);
                                    $imageExtensions = ['jpg', 'jpeg', 'png', 'gif'];
                                @endphp
                            
                                @if(in_array(strtolower($fileExtension), $imageExtensions))
                                    <a href="{{ asset(custom_file($registration->bill_image)) }}" target="_blank">
                                        <img src="{{ asset(custom_file($registration->bill_image)) }}" alt="Bill Image" width="50" height="50" class="img-thumbnail">
                                    </a>
                                @else
                                    <a href="{{ asset(custom_file($registration->bill_image)) }}" target="_blank">View PDF</a>
                                @endif
                            @else
                                No File
                            @endif
                        </td>
                        <td>{{ $registration->status ? translate('Approved') : translate('Pending') }}</td>
                        <td class="text-right">
                            @can('ban_customer')
                                @if ($registration->status != 1)
                                    <a href="#" class="btn btn-soft-success btn-sm"
                                        onclick="show_Approval_model({{ $registration->id }}, 'approve');"
                                        title="{{ translate('Approval') }}">
                                        Approve
                                    </a>
                                @else
                                    <a href="#" class="btn btn-soft-danger btn-sm"
                                        onclick="show_Approval_model({{ $registration->id }}, 'not_approve');"
                                        title="{{ translate('Not Approve') }}">
                                        Not Approve
                                    </a>
                                @endif
                            @endcan
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


</div>



{{-- - //------------------------------ approval modal -----------------------// -- --}}

<div class="modal fade" id="approval_model" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel_phone"
    aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content py-3">
            <div class="modal-header">
                <div class="heading">
                    <h5 class="modal-title" id="exampleModalLabel_phone">Approval</h5>
                </div>
                <div class="purple_btn_close">
                    <button type="button" class="close p-1 px-3" data-dismiss="modal" aria-label="Close">
                    </button>
                </div>
            </div>
            <form id="approval-status-model" action="{{ url(route('warranty_registration.update_status')) }}" method="post">
                @csrf

                <input type="hidden" name="id">

                <!-- Approval Status Dropdown -->
                <div class="form-group">
                    <label for="approval-status" class="modal-body col-form-label form-label">Approval Status:</label>
                    <select class="form-control" id="approval-status" name="approval_status"
                        onchange="toggleNote()">
                        <option value="approve">Approve</option>
                        <option value="not_approve">Not Approve</option>
                    </select>
                </div>

                <div id="note-section" style="display: none;" class="modal-body">
                    <div class="form-group">
                        <label for="recipient-name" class="col-form-label form-label">Note :</label>
                        <textarea type="text" class="form-control" id="note" name="note"></textarea>
                    </div>
                </div>


                <div class="modal-footer">
                    <div class="blue_btn">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                    </div>
                    <div class="purple_btn">
                        <button type="submit" class="btn btn-primary">Proceed</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- - //------------------------------ approval modal -----------------------// -- --}}



@endsection

@section('modal')
    <!-- Delete modal -->
    @include('modals.delete_modal')
    <!-- Bulk Delete modal -->
    @include('modals.bulk_delete_modal')
@endsection


@section('script')
    <script type="text/javascript">

        function toggleNote() {
            const approvalStatus = document.getElementById('approval-status').value;
            const noteSection = document.getElementById('note-section');

            if (approvalStatus === 'not_approve') {
                noteSection.style.display = 'block'; // Show the note section
            } else {
                noteSection.style.display = 'none'; // Hide the note section
            }
        }


        // // Global scope
        function show_Approval_model(id, status, role) {
            // // Set the value of the hidden input field
            $('#approval_model input[name="id"]').val(id);

            // Set the selected option in the dropdown
            $('#approval-status').val(status);

            toggleNote();

            // Show the modal
            $('#approval_model').modal('show');
        }

    </script>
@endsection
