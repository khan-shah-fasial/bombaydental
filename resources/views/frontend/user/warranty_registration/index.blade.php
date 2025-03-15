@extends('frontend.layouts.user_panel')

@section('panel_content')
    <div class="card shadow-none rounded-0 border">
        <div class="card-header border-bottom-0">
            <h5 class="mb-0 fs-20 fw-700 text-dark">{{ translate('Warranty Registration') }}</h5>
        </div>
        <div class="card-body">

            <!-- Warranty Registration Form -->
            <form action="{{ route('warranty_registration.store') }}" id="warranty_registration_store" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="row mb-3">
                    <div class="col-md-4">
                        <label>Product Name:</label>
                        <select name="product_id" class="form-control aiz-selectpicker" data-live-search="true" required>
                            <option value="">Select Product</option>
                            @foreach($products as $product)
                                <option value="{{ $product->id }}">{{ $product->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label>Serial Number:</label>
                        <input type="text" name="serial_no" class="form-control" required>
                    </div>
                    <div class="col-md-4">
                        <label>Date of Purchase:</label>
                        <input type="date" name="date_of_purchase" class="form-control" required>
                    </div>
                    <div class="col-md-4 mt-3">
                        <label>Upload Bill Image:</label>
                        <input type="file" name="bill_image" class="form-control" accept="image/*,application/pdf" required>
                    </div>
                    <div class="col-md-4 mt-3">
                        <button type="submit" class="btn btn-primary">Submit</button>
                    </div>
                </div>
            </form>


            <table class="table aiz-table mb-0">
                <thead class="text-gray fs-12">
                    <tr>
                        <th class="pl-0">Product Name</th>
                        <th>Serial No</th>
                        <th>Bill Image</th>
                        <th>Date Of Purchase</th>
                        <th>Status</th>
                        <th class="text-right pr-0">{{ translate('Options') }}</th>
                    </tr>
                </thead>
                <tbody class="fs-14">
                    @foreach ($warranty_registration as $key => $order)
                            <tr>

                                <td class="pl-0">
                                    {{ $order->product->name ?? 'N/A' }}
                                </td>
                                <td class="pl-0">
                                    {{ $order->serial_no  }}
                                </td>
                                <td>
                                    @if($order->bill_image)
                                        @php
                                            $fileExtension = pathinfo($order->bill_image, PATHINFO_EXTENSION);
                                            $imageExtensions = ['jpg', 'jpeg', 'png', 'gif'];
                                        @endphp
                                    
                                        @if(in_array(strtolower($fileExtension), $imageExtensions))
                                            <a href="{{ asset(custom_file($order->bill_image)) }}" target="_blank">
                                                <img src="{{ asset(custom_file($order->bill_image)) }}" alt="Bill Image" width="50" height="50" class="img-thumbnail">
                                            </a>
                                        @else
                                            <a href="{{ asset(custom_file($order->bill_image)) }}" target="_blank">View PDF</a>
                                        @endif
                                    @else
                                        No File
                                    @endif
                                </td>
                                <!-- Date -->
                                <td class="text-secondary">{{ date('d-m-Y', strtotime($order->date_of_purchase)) }}</td>
                                <!-- Amount -->
                                <td class="fw-700">
                                    {{ $order->status ? 'Approved' : 'Not Approved' }}
                                </td>
                                <!-- Options -->
                                <td class="text-right pr-0">

                                    @if ($order->status != '1')
                                        <!-- edit -->
                                        <button type="button" class="btn-soft-white rounded-3 btn-sm mr-1 openEditModal" data-id="{{ $order->id }}">
                                            {{ translate('Edit') }}
                                        </button>
                                        <!-- Cancel -->
                                        <a href="javascript:void(0)" class="btn btn-soft-danger btn-icon btn-circle btn-sm hov-svg-white mt-2 mt-sm-0 confirm-delete" data-href="{{route('warranty_registration.destroy', $order->id)}}" title="{{ translate('Cancel') }}">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="9.202" height="12" viewBox="0 0 9.202 12">
                                                <path id="Path_28714" data-name="Path 28714" d="M15.041,7.608l-.193,5.85a1.927,1.927,0,0,1-1.933,1.864H9.243A1.927,1.927,0,0,1,7.31,13.46L7.117,7.608a.483.483,0,0,1,.966-.032l.193,5.851a.966.966,0,0,0,.966.929h3.672a.966.966,0,0,0,.966-.931l.193-5.849a.483.483,0,1,1,.966.032Zm.639-1.947a.483.483,0,0,1-.483.483H6.961a.483.483,0,1,1,0-.966h1.5a.617.617,0,0,0,.615-.555,1.445,1.445,0,0,1,1.442-1.3h1.126a1.445,1.445,0,0,1,1.442,1.3.617.617,0,0,0,.615.555h1.5a.483.483,0,0,1,.483.483ZM9.913,5.178h2.333a1.6,1.6,0,0,1-.123-.456.483.483,0,0,0-.48-.435H10.516a.483.483,0,0,0-.48.435,1.6,1.6,0,0,1-.124.456ZM10.4,12.5V8.385a.483.483,0,0,0-.966,0V12.5a.483.483,0,1,0,.966,0Zm2.326,0V8.385a.483.483,0,0,0-.966,0V12.5a.483.483,0,1,0,.966,0Z" transform="translate(-6.478 -3.322)" fill="#d43533"/>
                                            </svg>
                                        </a>
                                    @endif
                                </td>
                            </tr>
                    @endforeach
                </tbody>
            </table>
            <!-- Pagination -->
            <div class="aiz-pagination mt-2">
                {{ $warranty_registration->links() }}
            </div>
        </div>
    </div>



    <!-- delete Modal -->
    <div id="delete-modal" class="modal fade">
        <div class="modal-dialog modal-sm modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title h6">{{translate('Delete Confirmation')}}</h4>
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true"></button>
                </div>
                <div class="modal-body text-center">
                    <p class="mt-1 fs-14">{{translate('Are you sure to delete this?')}}</p>
                    <button type="button" class="btn btn-secondary rounded-0 mt-2" data-dismiss="modal">{{translate('Cancel')}}</button>
                    <a href="" id="delete-link" class="btn btn-primary rounded-0 mt-2">{{translate('Delete')}}</a>
                </div>
            </div>
        </div>
    </div>

    <!-- /.modal -->

    <!-- Edit Modal -->
    <div class="modal fade" id="editWarrantyModal" tabindex="-1" aria-labelledby="editWarrantyLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="editWarrantyLabel">Edit Warranty Registration</h5>

                </div>
                <form id="editWarrantyForm" method="POST" enctype="multipart/form-data">
                    @csrf
                    @method('PUT')

                    <div class="modal-body">
                        <!-- Product Dropdown -->
                        <div class="mb-3">
                            <label for="product_id" class="form-label">Product Name</label>
                            <select name="product_id" id="product_id" class="form-select aiz-selectpicker" data-live-search="true" required>
                                @foreach($products as $product)
                                    <option value="{{ $product->id }}">{{ $product->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Serial Number -->
                        <div class="mb-3">
                            <label for="serial_no" class="form-label">Serial Number</label>
                            <input type="text" name="serial_no" id="serial_no" class="form-control" required>
                        </div>

                        <!-- Date of Purchase -->
                        <div class="mb-3">
                            <label for="date_of_purchase" class="form-label">Date of Purchase</label>
                            <input type="date" name="date_of_purchase" id="date_of_purchase" class="form-control" required>
                        </div>

                        <!-- Bill Image -->
                        <div class="mb-3">
                            <label for="bill_image" class="form-label">Bill Image (Optional)</label>
                            <input type="file" name="bill_image" id="bill_image" class="form-control">
                            <small class="text-muted">Leave blank if not updating the image.</small>
                            {{-- <img id="bill_image_preview" src="" width="50" height="50" class="img-thumbnail mt-2" style="display:none;"> --}}
                        </div>
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary">Update</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

@endsection

{{-- @section('modal')
    <!-- Delete modal -->
    @include('modals.delete_modal')

@endsection --}}

@section('custom_script')
<script>

    $(document).ready(function() {
        initValidate(`#warranty_registration_store`);
        $(`#warranty_registration_store`).on('submit', function (e) {
            var form = $(this);
            ajax_form_submit(e, form, function (response) {
                responseHandler(response);
            });
        });

        initValidate(`#editWarrantyForm`);
        $(`#editWarrantyForm`).on('submit', function (e) {
            var form = $(this);
            ajax_form_submit(e, form, function (response) {
                responseHandler(response);
            });
        });

        function responseHandler(response) {
            if (response.status == "success") {
                setTimeout(() => {
                    location.reload();
                }, 2000); // 2-second delay
            }
        }

    });

    $(document).on('click', '.openEditModal', function () {
        let id = $(this).data('id');

        // AJAX Request to Fetch Data
        $.ajax({
            url: `/warranty_registration/${id}/edit`,
            type: 'GET',
            success: function (response) {
                $('#product_id').val(response.product_id);
                $('#serial_no').val(response.serial_no);
                $('#date_of_purchase').val(response.date_of_purchase);

                // if (response.bill_image) {
                //     $('#bill_image_preview').attr('src', `../${response.bill_image}`).show();
                // } else {
                //     $('#bill_image_preview').hide();
                // }

                $('#editWarrantyForm').attr('action', `/warranty_registration/${id}`);
                $('#editWarrantyModal').modal('show');
            },
            error: function () {
                alert('Error fetching data.');
            }
        });
    });
</script>
@endsection

